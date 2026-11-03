<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InventoryIntelligenceService
{
    /**
     * Predicción de compras: productos con días de stock restantes basados en rotación.
     */
    public function getPredictions(int $days = 30): Collection
    {
        $since = Carbon::now()->subDays($days);

        $salesAvg = SaleItem::where('created_at', '>=', $since)
            ->selectRaw('product_id, SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->pluck('total_sold', 'product_id');

        return Product::where('stock', '>', 0)
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
                $product->risk_level = $this->calculateRiskLevel($product->days_remaining);

                return $product;
            })
            ->filter(fn ($p) => $p->avg_daily_sales > 0)
            ->sortBy('days_remaining')
            ->values();
    }

    /**
     * Lista automática de pedidos sugeridos basada en rotación real.
     *
     * @return array{orders: Collection, totalCost: float, totalToOrder: int, days: int, bufferDays: int}
     */
    public function getSuggestedOrders(int $days = 7, int $bufferDays = 2): array
    {
        $since = Carbon::now()->subDays($days)->startOfDay();

        $dailySales = SaleItem::where('created_at', '>=', $since)
            ->selectRaw('
                product_id,
                DATE(created_at) as sale_date,
                SUM(quantity) as daily_qty
            ')
            ->groupBy('product_id', 'sale_date')
            ->get()
            ->groupBy('product_id');

        $products = Product::orderBy('name')->get();

        $orders = $products->map(function ($product) use ($dailySales, $bufferDays) {
            $sales = $dailySales->get($product->id, collect())->pluck('daily_qty')->toArray();
            $totalSold = array_sum($sales);

            $avgDaily = count($sales) > 0 ? round($totalSold / count($sales), 2) : 0;
            $maxDaily = count($sales) > 0 ? max($sales) : 0;

            $targetStock = (int) ceil(($avgDaily * $bufferDays) + $maxDaily);
            $toOrder = max(0, $targetStock - $product->stock);
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
            ->sortByDesc('to_order')
            ->values();

        $totalCost = (float) $orders->sum('estimated_cost');
        $totalToOrder = (int) $orders->sum('to_order');

        return compact('orders', 'totalCost', 'totalToOrder', 'days', 'bufferDays');
    }

    public function calculateRiskLevel(?float $daysRemaining): string
    {
        if ($daysRemaining === null) {
            return 'none';
        }
        if ($daysRemaining <= 3) {
            return 'critical';
        }
        if ($daysRemaining <= 7) {
            return 'warning';
        }
        if ($daysRemaining <= 14) {
            return 'low';
        }

        return 'safe';
    }
}
