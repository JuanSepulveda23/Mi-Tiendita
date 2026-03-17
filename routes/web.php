<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IntelligenceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Offline page (accessible without auth for PWA)
Route::get('/offline', fn () => view('offline'))->name('offline');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Productos
    Route::resource('products', ProductController::class)->except(['show']);
    Route::post('/products/{product}/toggle-frequent', [ProductController::class, 'toggleFrequent'])->name('products.toggleFrequent');
    Route::post('/products/quick-store', [ProductController::class, 'quickStore'])->name('products.quickStore');

    // Ventas — Formulario clásico
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');

    // Ventas — Modo Caja (POS)
    Route::get('/pos', [SaleController::class, 'pos'])->name('pos');
    Route::post('/pos/sell', [SaleController::class, 'storeQuick'])->name('pos.sell');
    Route::get('/pos/search', [SaleController::class, 'searchProducts'])->name('pos.search');

    // Ventas del día
    Route::get('/sales/today', [SaleController::class, 'today'])->name('sales.today');

    // Inteligencia: Predicciones y Pedidos
    Route::get('/intelligence/predictions', [IntelligenceController::class, 'predictions'])->name('intelligence.predictions');
    Route::get('/intelligence/orders', [IntelligenceController::class, 'orders'])->name('intelligence.orders');
    Route::get('/api/intelligence/predictions', [IntelligenceController::class, 'predictionsApi'])->name('api.intelligence.predictions');

    // Reportes de ganancias
    Route::get('/reports/profits', [ReportController::class, 'profits'])->name('reports.profits');
    Route::get('/reports/profits/data', [ReportController::class, 'profitsData'])->name('reports.profits.data');
    Route::get('/reports/profits/by-product', [ReportController::class, 'profitsByProduct'])->name('reports.profits.byProduct');

    // Alertas de vencimiento
    Route::get('/alerts/expirations', [AlertController::class, 'expirations'])->name('alerts.expirations');
    Route::post('/alerts/{alert}/dismiss', [AlertController::class, 'dismiss'])->name('alerts.dismiss');
    Route::post('/alerts/product/{productId}/dismiss-all', [AlertController::class, 'dismissProduct'])->name('alerts.dismissProduct');
    Route::get('/api/alerts/urgent', [AlertController::class, 'activeAlertsApi'])->name('api.alerts.urgent');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
