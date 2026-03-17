<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class IntelligenceController extends Controller
{
    /**
     * Predicción de compras: muestra cuántos días de stock quedan por producto.
     */
    public function predictions(): View
    {
        $days = 30;
        $since = Carbon::now()->subDays($days);

        // Average daily sales per product over the last N days
        $salesAvg = SaleItem::where('sale_items.created_at', '>=', $since)
            ->selectRaw('product_id, SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->pluck('total_sold', 'product_id');

        $products = Product::where('stock', '>', 0)
            ->orWhereIn('id', $salesAvg->keys())
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($salesAvg, $days) {
                $totalSold = $salesAvg->get($product->id, 0);
                $avgDaily = $totalSold / $days;

                $product->avg_daily_sales = round($avgDaily, 2);
                $product->days_remaining = $avgDaily > 0
                    ? round($product->stock / $avgDaily, 1)
                    : null;
                $product->risk_level = $this->riskLevel($product->days_remaining);

                return $product;
            })
            ->filter(fn ($p) => $p->avg_daily_sales > 0)
            ->sortBy('days_remaining');

        return view('intelligence.predictions', compact('products'));
    }

    /**
     * Lista automática de pedidos sugeridos basada en rotación.
     * "Si hoy vendí 10, compro 10 mañana"
     */
    public function orders(): View
    {
        $days = 7; // Analizar últimos 7 días para rotación real
        $bufferDays = 2; // Mantener stock para 2 días adicionales
        $since = Carbon::now()->subDays($days)->startOfDay();

        // Ventas diarias por producto (últimos 7 días)
        $dailySales = SaleItem::where('sale_items.created_at', '>=', $since)
            ->selectRaw('
                product_id,
                DATE(sale_items.created_at) as sale_date,
                SUM(quantity) as daily_qty
            ')
            ->groupBy('product_id', 'sale_date')
            ->get()
            ->groupBy('product_id');

        $products = Product::orderBy('name')->get();

        $orders = $products->map(function ($product) use ($dailySales, $days, $bufferDays) {
            // Obtener ventas de los últimos 7 días de este producto
            $sales = $dailySales->get($product->id, collect())->pluck('daily_qty')->toArray();
            $totalSold = array_sum($sales);
            
            // Promedio diario real de los últimos 7 días
            $avgDaily = count($sales) > 0 ? round($totalSold / count($sales), 2) : 0;
            $maxDaily = count($sales) > 0 ? max($sales) : 0;

            // Stock objetivo: (promedio * buffer days) + máximo diario
            // Esto asegura que siempre tengas stock suficiente incluso en días altos
            $targetStock = ceil(($avgDaily * $bufferDays) + $maxDaily);
            
            // Cantidad a comprar = target - stock actual
            $toOrder = max(0, $targetStock - $product->stock);

            // Días de rotación: cuántos días durará el stock actual
            $rotationDays = $avgDaily > 0 ? round($product->stock / $avgDaily, 1) : null;

            return (object) [
                'product' => $product,
                'daily_sales' => $sales,
                'avg_daily' => $avgDaily,
                'max_daily' => (int) $maxDaily,
                'current_stock' => $product->stock,
                'target_stock' => $targetStock,
                'to_order' => $toOrder,
                'estimated_cost' => round($toOrder * $product->purchase_price, 2),
                'rotation_days' => $rotationDays,
            ];
        })
        ->filter(fn ($o) => $o->to_order > 0)
        ->sortByDesc('to_order');

        $totalCost = $orders->sum('estimated_cost');
        $totalToOrder = $orders->sum('to_order');

        return view('intelligence.orders', compact('orders', 'totalCost', 'totalToOrder', 'days', 'bufferDays'));
    }

    /**
     * API: datos para predicción (JSON).
     */
    public function predictionsApi(): JsonResponse
    {
        $days = 30;
        $since = Carbon::now()->subDays($days);

        $salesAvg = SaleItem::where('sale_items.created_at', '>=', $since)
            ->selectRaw('product_id, SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->pluck('total_sold', 'product_id');

        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($salesAvg, $days) {
                $totalSold = $salesAvg->get($product->id, 0);
                $avgDaily = $totalSold / $days;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'avg_daily' => round($avgDaily, 2),
                    'days_remaining' => $avgDaily > 0 ? round($product->stock / $avgDaily, 1) : null,
                ];
            })
            ->filter(fn ($p) => $p['avg_daily'] > 0)
            ->sortBy('days_remaining')
            ->values();

        return response()->json($products);
    }

    private function riskLevel(?float $daysRemaining): string
    {
        if ($daysRemaining === null) return 'none';
        if ($daysRemaining <= 3) return 'critical';
        if ($daysRemaining <= 7) return 'warning';
        if ($daysRemaining <= 14) return 'low';
        return 'safe';
    }
}
