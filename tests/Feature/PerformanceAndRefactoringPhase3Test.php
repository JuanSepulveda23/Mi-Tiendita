<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\ExpirationAlertService;
use App\Services\InventoryIntelligenceService;
use App\Services\ProfitReportService;
use App\Services\SaleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceAndRefactoringPhase3Test extends TestCase
{
    use RefreshDatabase;

    public function test_profit_report_service_aggregates_daily_data_correctly(): void
    {
        $product = Product::create([
            'name' => 'Bebida Energizante',
            'purchase_price' => 3000,
            'sale_price' => 5000,
            'stock' => 50,
        ]);

        $saleService = app(SaleService::class);
        $reportService = app(ProfitReportService::class);

        // Venta realizada hoy: 2 unidades = 10,000 venta, 6,000 costo, 4,000 ganancia
        $saleService->createQuickSale([
            ['product_id' => $product->id, 'quantity' => 2],
        ]);

        // Simular una venta de ayer usando forceFill para preservar timestamps
        $yesterday = Carbon::yesterday();
        $pastSale = new Sale(['total' => 5000]);
        $pastSale->forceFill(['created_at' => $yesterday, 'updated_at' => $yesterday])->save();

        $pastItem = $pastSale->items()->make([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 5000,
            'cost_price' => 3000,
        ]);
        $pastItem->forceFill(['created_at' => $yesterday, 'updated_at' => $yesterday])->save();

        // Obtener datos de últimos 3 días
        $data = $reportService->getProfitsData(3);

        $this->assertCount(4, $data); // días 3, 2, 1, 0

        $todayKey = Carbon::today()->toDateString();
        $yesterdayKey = Carbon::yesterday()->toDateString();

        $todayRow = collect($data)->firstWhere('date', $todayKey);
        $this->assertNotNull($todayRow);
        $this->assertEquals(10000.0, $todayRow['revenue']);
        $this->assertEquals(6000.0, $todayRow['cost']);
        $this->assertEquals(4000.0, $todayRow['profit']);
        $this->assertEquals(1, $todayRow['sales_count']);

        $yesterdayRow = collect($data)->firstWhere('date', $yesterdayKey);
        $this->assertNotNull($yesterdayRow);
        $this->assertEquals(5000.0, $yesterdayRow['revenue']);
        $this->assertEquals(3000.0, $yesterdayRow['cost']);
        $this->assertEquals(2000.0, $yesterdayRow['profit']);
        $this->assertEquals(1, $yesterdayRow['sales_count']);
    }

    public function test_profits_data_endpoint_executes_within_few_queries(): void
    {
        $admin = User::factory()->admin()->create();

        // Monitor query count
        DB::enableQueryLog();

        $response = $this->actingAs($admin)->getJson(route('reports.profits.data', ['days' => 30]));

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['date', 'revenue', 'cost', 'profit', 'margin', 'sales_count'],
        ]);

        // Debe ejecutarse en muy pocas consultas (autenticación + la consulta agregada única),
        // NUNCA 60 o 70 consultas en bucle
        $this->assertLessThan(6, count($queries), 'La consulta profitsData ejecutó demasiadas queries; debe usar agregación única.');
    }

    public function test_inventory_intelligence_service_predictions_and_orders(): void
    {
        $product = Product::create([
            'name' => 'Arroz Diana 1kg',
            'purchase_price' => 3500,
            'sale_price' => 4500,
            'stock' => 5,
        ]);

        $saleService = app(SaleService::class);
        $intelligenceService = app(InventoryIntelligenceService::class);

        // Vender 2 unidades
        $saleService->createQuickSale([
            ['product_id' => $product->id, 'quantity' => 2],
        ]);

        $predictions = $intelligenceService->getPredictions(30);
        $this->assertTrue($predictions->isNotEmpty());

        $orders = $intelligenceService->getSuggestedOrders(7, 2);
        $this->assertArrayHasKey('orders', $orders);
        $this->assertArrayHasKey('totalCost', $orders);
    }

    public function test_expiration_alert_service_works_seamlessly_with_sqlite(): void
    {
        $product = Product::create([
            'name' => 'Queso Campesino',
            'purchase_price' => 4000,
            'sale_price' => 6000,
            'stock' => 10,
            'expiration_date' => Carbon::today()->addDays(2),
        ]);

        $alertService = app(ExpirationAlertService::class);

        $created = $alertService->checkAndGenerateAlerts();
        $this->assertEquals(1, $created);

        // Validar paginación sin errores de función FIELD() en SQLite
        $paginated = $alertService->getAlertsPaginated(10);
        $this->assertCount(1, $paginated->items());

        $stats = $alertService->getActiveAlertsStats();
        $this->assertEquals(1, $stats['total']);
    }
}
