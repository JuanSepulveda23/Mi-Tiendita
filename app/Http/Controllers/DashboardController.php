<?php

namespace App\Http\Controllers;

use App\Models\ExpirationAlert;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        // ── KPI principales ──
        $salesToday       = Sale::whereDate('created_at', $today)->sum('total');
        $salesCountToday  = Sale::whereDate('created_at', $today)->count();
        $productsSoldToday = SaleItem::whereHas('sale', fn ($q) => $q->whereDate('created_at', $today))->sum('quantity');

        // Ganancia estimada del día (venta - costo)
        $profitToday = SaleItem::whereHas('sale', fn ($q) => $q->whereDate('created_at', $today))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(sale_items.quantity * (sale_items.price - products.purchase_price)) as profit')
            ->value('profit') ?? 0;

        // ── Totales generales ──
        $totalSales    = Sale::sum('total');
        $totalProducts = Product::count();
        $salesCount    = Sale::count();

        // ── Alertas de stock bajo (usa min_stock del producto) ──
        $lowStockProducts = Product::lowStock()
            ->orderBy('stock')
            ->get();
        $lowStock = $lowStockProducts->count();

        // ── Últimas ventas ──
        $recentSales = Sale::with('items.product')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // ── Productos más vendidos hoy ──
        $topProductsToday = SaleItem::whereHas('sale', fn ($q) => $q->whereDate('created_at', $today))
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(quantity * price) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->with('product')
            ->get();

        // ── Alertas de vencimiento urgentes ──
        $urgentExpirationAlerts = ExpirationAlert::with('product')
            ->active()
            ->urgent()
            ->orderBy('days_remaining')
            ->take(5)
            ->get();
        $expirationWarningCount = ExpirationAlert::active()->willExpire()->count();

        return view('dashboard', compact(
            'salesToday',
            'salesCountToday',
            'productsSoldToday',
            'profitToday',
            'totalSales',
            'totalProducts',
            'lowStock',
            'salesCount',
            'lowStockProducts',
            'recentSales',
            'topProductsToday',
            'urgentExpirationAlerts',
            'expirationWarningCount'
        ));
    }
}
