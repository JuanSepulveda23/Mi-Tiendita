<?php

namespace App\Services;

use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProfitReportService
{
    /**
     * Obtener datos consolidados de ganancias por día optimizados en una única consulta SQL.
     *
     * @return array<int, array{date: string, revenue: float, cost: float, profit: float, margin: float, sales_count: int}>
     */
    public function getProfitsData(int $days = 30): array
    {
        $days = min(max(1, $days), 365);
        $since = Carbon::now()->subDays($days)->startOfDay();

        // Consulta agregada única agrupada por fecha: elimina el problema de N+1 queries
        $aggregated = DB::table('sales')
            ->leftJoin('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.created_at', '>=', $since)
            ->selectRaw('
                DATE(sales.created_at) as sale_date,
                COUNT(DISTINCT sales.id) as sales_count,
                COALESCE(SUM(sale_items.quantity * sale_items.price), 0) as revenue,
                COALESCE(SUM(sale_items.quantity * sale_items.cost_price), 0) as cost
            ')
            ->groupByRaw('DATE(sales.created_at)')
            ->get()
            ->keyBy('sale_date');

        $dailyData = [];

        // Generar la serie continua de días en memoria (0 consultas SQL en el bucle)
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $record = $aggregated->get($date);

            $revenue = $record ? (float) $record->revenue : 0.0;
            $cost = $record ? (float) $record->cost : 0.0;
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0.0;
            $salesCount = $record ? (int) $record->sales_count : 0;

            $dailyData[] = [
                'date' => $date,
                'revenue' => round($revenue, 2),
                'cost' => round($cost, 2),
                'profit' => round($profit, 2),
                'margin' => $margin,
                'sales_count' => $salesCount,
            ];
        }

        return $dailyData;
    }

    /**
     * API: Ganancias detalladas por producto en los últimos N días.
     */
    public function getProfitsByProduct(int $days = 30, int $limit = 15): Collection
    {
        $days = min(max(1, $days), 365);
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
                SUM(sale_items.quantity * sale_items.cost_price) as total_cost,
                SUM(sale_items.quantity * (sale_items.price - sale_items.cost_price)) as total_profit
            ')
            ->groupBy('products.id', 'products.name', 'products.purchase_price', 'products.sale_price')
            ->orderByDesc('total_profit')
            ->take($limit)
            ->get();

        return $products->map(fn ($p) => [
            'name' => $p->name,
            'purchase_price' => (float) $p->purchase_price,
            'sale_price' => (float) $p->sale_price,
            'total_qty' => (int) $p->total_qty,
            'revenue' => round((float) $p->total_revenue, 2),
            'cost' => round((float) $p->total_cost, 2),
            'profit' => round((float) $p->total_profit, 2),
            'margin' => $p->total_revenue > 0
                ? round(($p->total_profit / $p->total_revenue) * 100, 1) : 0,
        ]);
    }
}
