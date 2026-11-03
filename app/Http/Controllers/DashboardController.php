<?php

namespace App\Http\Controllers;

use App\Models\ExpirationAlert;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\ExpirationAlertService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ExpirationAlertService $alertService
    ) {}

    public function index(): View
    {
        $today = Carbon::today();

        // ── KPI principales ──
        $salesToday = Sale::whereDate('created_at', $today)->sum('total');
        $salesCountToday = Sale::whereDate('created_at', $today)->count();
        $productsSoldToday = SaleItem::whereHas('sale', fn ($q) => $q->whereDate('created_at', $today))->sum('quantity');

        // Ganancia estimada del día (venta - costo histórico) - Exclusivo para Administradores
        $profitToday = request()->user()?->isAdmin()
            ? (SaleItem::whereHas('sale', fn ($q) => $q->whereDate('created_at', $today))
                ->selectRaw('SUM(quantity * (price - cost_price)) as profit')
                ->value('profit') ?? 0)
            : 0;

        // ── Totales generales ──
        $totalSales = Sale::sum('total');
        $totalProducts = Product::count();
        $salesCount = Sale::count();

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
        $urgentExpirationAlerts = $this->alertService->getActiveUrgentAlerts(5);
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
