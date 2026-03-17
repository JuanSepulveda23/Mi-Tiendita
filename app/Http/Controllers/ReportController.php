<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function profits(): View
    {
        return view('reports.profits');
    }

    /**
     * API: Datos de ganancias diarias para los últimos N días.
     */
    public function profitsData(Request $request): JsonResponse
    {
        $days = min((int) $request->input('days', 30), 365);
        $since = Carbon::now()->subDays($days)->startOfDay();

        $dailyData = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();

            $salesTotal = Sale::whereDate('created_at', $date)->sum('total');

            $costAndRevenue = SaleItem::whereHas('sale', fn ($q) => $q->whereDate('created_at', $date))
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->selectRaw('
                    COALESCE(SUM(sale_items.quantity * sale_items.price), 0) as revenue,
                    COALESCE(SUM(sale_items.quantity * products.purchase_price), 0) as cost
                ')
                ->first();

            $revenue = (float) ($costAndRevenue->revenue ?? 0);
            $cost = (float) ($costAndRevenue->cost ?? 0);
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0;

            $dailyData[] = [
                'date' => $date,
                'revenue' => round($revenue, 2),
                'cost' => round($cost, 2),
                'profit' => round($profit, 2),
                'margin' => $margin,
                'sales_count' => Sale::whereDate('created_at', $date)->count(),
            ];
        }

        return response()->json($dailyData);
    }

    /**
     * API: Ganancias por producto (top N).
     */
    public function profitsByProduct(Request $request): JsonResponse
    {
        $days = min((int) $request->input('days', 30), 365);
        $since = Carbon::now()->subDays($days)->startOfDay();

        $products = SaleItem::whereHas('sale', fn ($q) => $q->where('created_at', '>=', $since))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->selectRaw('
                products.id,
                products.name,
                products.purchase_price,
                products.sale_price,
                SUM(sale_items.quantity) as total_qty,
                SUM(sale_items.quantity * sale_items.price) as total_revenue,
                SUM(sale_items.quantity * products.purchase_price) as total_cost,
                SUM(sale_items.quantity * (sale_items.price - products.purchase_price)) as total_profit
            ')
            ->groupBy('products.id', 'products.name', 'products.purchase_price', 'products.sale_price')
            ->orderByDesc('total_profit')
            ->take(15)
            ->get();

        return response()->json($products->map(fn ($p) => [
            'name' => $p->name,
            'purchase_price' => (float) $p->purchase_price,
            'sale_price' => (float) $p->sale_price,
            'total_qty' => (int) $p->total_qty,
            'revenue' => round((float) $p->total_revenue, 2),
            'cost' => round((float) $p->total_cost, 2),
            'profit' => round((float) $p->total_profit, 2),
            'margin' => $p->total_revenue > 0
                ? round(($p->total_profit / $p->total_revenue) * 100, 1) : 0,
        ]));
    }
}
