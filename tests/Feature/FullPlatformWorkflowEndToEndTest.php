<?php

namespace Tests\Feature;

use App\Models\ExpirationAlert;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullPlatformWorkflowEndToEndTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper para crear productos consistentes en las pruebas.
     */
    private function createProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Producto Prueba '.uniqid(),
            'purchase_price' => 2000.00,
            'sale_price' => 3500.00,
            'stock' => 50,
            'min_stock' => 5,
        ], $attributes));
    }

    /**
     * 1. REDIRECCIONES DE INVITADOS (GUEST)
     * Verifica que cualquier usuario no autenticado sea redirigido a /login al intentar acceder
     * a cada una de las rutas protegidas del sistema.
     */
    public function test_unauthenticated_guests_are_redirected_to_login_for_all_protected_routes(): void
    {
        $protectedRoutes = [
            'dashboard' => '/dashboard',
            'products.index' => '/products',
            'products.create' => '/products/create',
            'pos' => '/pos',
            'sales.index' => '/sales',
            'sales.create' => '/sales/create',
            'sales.today' => '/sales/today',
            'alerts.expirations' => '/alerts/expirations',
            'reports.profits' => '/reports/profits',
            'intelligence.predictions' => '/intelligence/predictions',
            'intelligence.orders' => '/intelligence/orders',
            'profile.edit' => '/profile',
            'register' => '/register',
        ];

        foreach ($protectedRoutes as $name => $uri) {
            $response = $this->get($uri);
            $response->assertStatus(302);
            $response->assertRedirect('/login');
        }

        // Ruta raíz '/' redirige a '/dashboard' y consecuentemente a '/login' si es guest
        $rootResponse = $this->get('/');
        $rootResponse->assertStatus(302);
        $rootResponse->assertRedirect('/dashboard');
    }

    /**
     * 2. CONTROL DE ACCESO POR ROL (RBAC) Y REDIRECCIONES CAJERO VS ADMIN
     * Verifica que un cajero tenga acceso a ventas, pos y catálogo (lectura),
     * pero sea bloqueado con 403 Forbidden al intentar acciones administrativas.
     */
    public function test_role_based_access_control_and_redirects_for_cashier_vs_admin(): void
    {
        $cashier = User::factory()->create(['role' => User::ROLE_CASHIER]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $product = $this->createProduct();

        // ── CAJERO: Rutas permitidas ──
        $this->actingAs($cashier)->get('/dashboard')->assertStatus(200);
        $this->actingAs($cashier)->get('/products')->assertStatus(200);
        $this->actingAs($cashier)->get('/pos')->assertStatus(200);
        $this->actingAs($cashier)->get('/pos/search?q=test')->assertStatus(200);
        $this->actingAs($cashier)->get('/sales')->assertStatus(200);
        $this->actingAs($cashier)->get('/sales/today')->assertStatus(200);

        // ── CAJERO: Rutas restringidas (403 Forbidden) ──
        $this->actingAs($cashier)->get('/products/create')->assertStatus(403);
        $this->actingAs($cashier)->post('/products', ['name' => 'Hack'])->assertStatus(403);
        $this->actingAs($cashier)->get("/products/{$product->id}/edit")->assertStatus(403);
        $this->actingAs($cashier)->put("/products/{$product->id}", ['name' => 'Hack'])->assertStatus(403);
        $this->actingAs($cashier)->delete("/products/{$product->id}")->assertStatus(403);
        $this->actingAs($cashier)->get('/reports/profits')->assertStatus(403);
        $this->actingAs($cashier)->get('/reports/profits/data')->assertStatus(403);
        $this->actingAs($cashier)->get('/reports/profits/by-product')->assertStatus(403);
        $this->actingAs($cashier)->get('/register')->assertStatus(403);
        $this->actingAs($cashier)->post('/register', ['name' => 'Nuevo'])->assertStatus(403);

        // ── ADMINISTRADOR: Acceso completo a las rutas restringidas ──
        $this->actingAs($admin)->get('/products/create')->assertStatus(200);
        $this->actingAs($admin)->get("/products/{$product->id}/edit")->assertStatus(200);
        $this->actingAs($admin)->get('/reports/profits')->assertStatus(200);
        $this->actingAs($admin)->get('/reports/profits/data')->assertStatus(200);
        $this->actingAs($admin)->get('/reports/profits/by-product')->assertStatus(200);
        $this->actingAs($admin)->get('/register')->assertStatus(200);
    }

    /**
     * 3. GESTIÓN COMPLETA DE PRODUCTOS (CRUD) Y REDIRECCIONES CON MENSAJES FLASH
     */
    public function test_admin_product_crud_lifecycle_with_validation_and_redirects(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // A. Creación de Producto con todos los campos validados
        $response = $this->actingAs($admin)->post('/products', [
            'name' => 'Café Especial Colombiano 500g',
            'barcode' => '7701122334455',
            'purchase_price' => 12000.00,
            'sale_price' => 18000.00,
            'stock' => 80,
            'min_stock' => 15,
            'entry_date' => Carbon::now()->subDays(10)->toDateString(),
            'expiration_date' => Carbon::now()->addMonths(6)->toDateString(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Producto creado exitosamente.');

        $product = Product::where('barcode', '7701122334455')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Café Especial Colombiano 500g', $product->name);
        $this->assertEquals(12000.00, (float) $product->purchase_price);
        $this->assertEquals(18000.00, (float) $product->sale_price);
        $this->assertEquals(80, $product->stock);

        // B. Edición y Actualización de Producto
        $updateResponse = $this->actingAs($admin)->put("/products/{$product->id}", [
            'name' => 'Café Especial Colombiano Premium 500g',
            'barcode' => '7701122334455',
            'purchase_price' => 13500.00,
            'sale_price' => 21000.00,
            'stock' => 95,
            'min_stock' => 20,
            'entry_date' => Carbon::now()->subDays(5)->toDateString(),
            'expiration_date' => Carbon::now()->addMonths(8)->toDateString(),
        ]);

        $updateResponse->assertStatus(302);
        $updateResponse->assertRedirect(route('products.index'));
        $updateResponse->assertSessionHas('success', 'Producto actualizado exitosamente.');

        $product->refresh();
        $this->assertEquals('Café Especial Colombiano Premium 500g', $product->name);
        $this->assertEquals(13500.00, (float) $product->purchase_price);
        $this->assertEquals(21000.00, (float) $product->sale_price);

        // C. Alternar producto frecuente en POS (AJAX)
        $toggleResponse = $this->actingAs($admin)->post("/products/{$product->id}/toggle-frequent");
        $toggleResponse->assertStatus(200);
        $toggleResponse->assertJson(['success' => true, 'is_frequent' => true]);

        // D. Registro rápido desde modal POS (AJAX)
        $quickResponse = $this->actingAs($admin)->post('/products/quick-store', [
            'name' => 'Gaseosa Mini 250ml',
            'sale_price' => 2000,
            'stock' => 48,
        ]);
        $quickResponse->assertStatus(200);
        $quickResponse->assertJson(['success' => true, 'message' => 'Producto creado exitosamente.']);
        $this->assertDatabaseHas('products', ['name' => 'Gaseosa Mini 250ml', 'stock' => 48]);

        // E. Eliminación de producto
        $deleteResponse = $this->actingAs($admin)->delete("/products/{$product->id}");
        $deleteResponse->assertStatus(302);
        $deleteResponse->assertRedirect(route('products.index'));
        $deleteResponse->assertSessionHas('success', 'Producto eliminado exitosamente.');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * 4. RASTREO DE PRODUCTO POR 30 DÍAS Y SU REPRESENTACIÓN EN LAS GRÁFICAS Y REPORTES
     * Simula la creación de productos y una serie histórica de ventas distribuida
     * a lo largo de 30 días continuos. Verifica que la API de la gráfica de ganancias
     * retorne exactamente los 31 días con ingresos, costos históricos, utilidades y márgenes.
     */
    public function test_product_tracked_for_thirty_days_and_accurately_represented_in_charts(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // Crear 2 productos con costo y precio definidos
        $productA = $this->createProduct([
            'name' => 'Arroz Diana 1kg',
            'purchase_price' => 2500.00,
            'sale_price' => 4000.00,
            'stock' => 300,
        ]);

        $productB = $this->createProduct([
            'name' => 'Aceite Premier 1000ml',
            'purchase_price' => 12000.00,
            'sale_price' => 17000.00,
            'stock' => 150,
        ]);

        // ── Simulación histórica de ventas a lo largo de los últimos 30 días ──
        $salesSchedule = [
            // Día -28: 2 unidades de Arroz
            28 => [
                ['product' => $productA, 'qty' => 2, 'price' => 4000.00, 'cost' => 2500.00],
            ],
            // Día -21: 4 unidades de Arroz y 1 de Aceite
            21 => [
                ['product' => $productA, 'qty' => 4, 'price' => 4000.00, 'cost' => 2500.00],
                ['product' => $productB, 'qty' => 1, 'price' => 17000.00, 'cost' => 12000.00],
            ],
            // Día -14: 2 unidades de Aceite
            14 => [
                ['product' => $productB, 'qty' => 2, 'price' => 17000.00, 'cost' => 12000.00],
            ],
            // Día -7: 5 unidades de Arroz
            7 => [
                ['product' => $productA, 'qty' => 5, 'price' => 4000.00, 'cost' => 2500.00],
            ],
            // Día -2: 3 unidades de Arroz y 2 de Aceite
            2 => [
                ['product' => $productA, 'qty' => 3, 'price' => 4000.00, 'cost' => 2500.00],
                ['product' => $productB, 'qty' => 2, 'price' => 17000.00, 'cost' => 12000.00],
            ],
            // Día 0 (Hoy): 1 unidad de Arroz
            0 => [
                ['product' => $productA, 'qty' => 1, 'price' => 4000.00, 'cost' => 2500.00],
            ],
        ];

        $totalExpectedRevenue = 0.0;
        $totalExpectedCost = 0.0;

        foreach ($salesSchedule as $daysAgo => $items) {
            $date = Carbon::now()->subDays($daysAgo)->startOfDay()->addHours(14);
            $saleTotal = 0;

            foreach ($items as $itemData) {
                $saleTotal += $itemData['qty'] * $itemData['price'];
                $totalExpectedRevenue += $itemData['qty'] * $itemData['price'];
                $totalExpectedCost += $itemData['qty'] * $itemData['cost'];
            }

            $sale = Sale::create(['total' => $saleTotal]);
            $sale->forceFill(['created_at' => $date, 'updated_at' => $date])->save();

            foreach ($items as $itemData) {
                $saleItem = new SaleItem([
                    'sale_id' => $sale->id,
                    'product_id' => $itemData['product']->id,
                    'quantity' => $itemData['qty'],
                    'price' => $itemData['price'],
                    'cost_price' => $itemData['cost'],
                ]);
                $saleItem->forceFill(['created_at' => $date, 'updated_at' => $date])->save();
            }
        }

        $totalExpectedProfit = $totalExpectedRevenue - $totalExpectedCost;

        // Modificamos el precio de compra actual del producto en el catálogo
        // para comprobar que el histórico en reportes permanece inmutable.
        $productA->update(['purchase_price' => 3800.00]);

        // ── A. CONSULTA A LA API DE LA GRÁFICA DE 30 DÍAS ──
        $response = $this->actingAs($admin)->getJson('/reports/profits/data?days=30');
        $response->assertStatus(200);

        $chartData = $response->json();

        // La gráfica debe contener exactamente 31 puntos continuos (Día -30 a Día 0)
        $this->assertCount(31, $chartData);

        // Verificamos sumatorias totales consolidadas para las series de la gráfica
        $sumRevenue = array_sum(array_column($chartData, 'revenue'));
        $sumCost = array_sum(array_column($chartData, 'cost'));
        $sumProfit = array_sum(array_column($chartData, 'profit'));

        $this->assertEquals(round($totalExpectedRevenue, 2), round($sumRevenue, 2));
        $this->assertEquals(round($totalExpectedCost, 2), round($sumCost, 2));
        $this->assertEquals(round($totalExpectedProfit, 2), round($sumProfit, 2));

        // Verificamos un punto específico de la gráfica: Día -21
        $date21 = Carbon::now()->subDays(21)->toDateString();
        $record21 = collect($chartData)->firstWhere('date', $date21);
        $this->assertNotNull($record21);

        // 4 Arroz ($16,000 rev, $10,000 cost) + 1 Aceite ($17,000 rev, $12,000 cost) = $33,000 rev, $22,000 cost
        $this->assertEquals(33000.00, $record21['revenue']);
        $this->assertEquals(22000.00, $record21['cost']);
        $this->assertEquals(11000.00, $record21['profit']);
        $this->assertEquals(33.3, $record21['margin']);
        $this->assertEquals(1, $record21['sales_count']);

        // Verificamos que días sin ventas tengan ceros limpios
        $dateEmpty = Carbon::now()->subDays(10)->toDateString();
        $recordEmpty = collect($chartData)->firstWhere('date', $dateEmpty);
        $this->assertNotNull($recordEmpty);
        $this->assertEquals(0, $recordEmpty['revenue']);
        $this->assertEquals(0, $recordEmpty['cost']);
        $this->assertEquals(0, $recordEmpty['profit']);
        $this->assertEquals(0, $recordEmpty['sales_count']);

        // ── B. CONSULTA AL DESGLOSE DE PRODUCTOS EN LA GRÁFICA (TOP PRODUCTOS) ──
        $productBreakdownResponse = $this->actingAs($admin)->getJson('/reports/profits/by-product?days=30');
        $productBreakdownResponse->assertStatus(200);

        $productsData = collect($productBreakdownResponse->json());

        // Aceite Premier: 5 unidades vendidas a $17,000 ($85,000 rev), costo histórico $12,000 ($60,000 cost), ganancia $25,000
        $aceiteStats = $productsData->firstWhere('name', 'Aceite Premier 1000ml');
        $this->assertNotNull($aceiteStats);
        $this->assertEquals(5, $aceiteStats['total_qty']);
        $this->assertEquals(85000.00, $aceiteStats['revenue']);
        $this->assertEquals(60000.00, $aceiteStats['cost']);
        $this->assertEquals(25000.00, $aceiteStats['profit']);
        $this->assertEquals(29.4, $aceiteStats['margin']);

        // Arroz Diana: 15 unidades vendidas a $4,000 ($60,000 rev), costo histórico $2,500 ($37,500 cost), ganancia $22,500
        $arrozStats = $productsData->firstWhere('name', 'Arroz Diana 1kg');
        $this->assertNotNull($arrozStats);
        $this->assertEquals(15, $arrozStats['total_qty']);
        $this->assertEquals(60000.00, $arrozStats['revenue']);
        $this->assertEquals(37500.00, $arrozStats['cost']);
        $this->assertEquals(22500.00, $arrozStats['profit']);
        $this->assertEquals(37.5, $arrozStats['margin']);
    }

    /**
     * 5. INTELIGENCIA DE INVENTARIO: ROTACIÓN DE 30 DÍAS Y PREDICCIÓN DE COMPRAS
     * Evalúa que la rotación de los últimos 30 días clasifique correctamente
     * los productos en niveles de riesgo (critical, warning, safe) y genere pedidos sugeridos.
     */
    public function test_thirty_day_rotation_drives_inventory_intelligence_and_demand_predictions(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $cashier = User::factory()->create(['role' => User::ROLE_CASHIER]);

        // Verificar que el cajero no tiene acceso a inteligencia de inventario
        $this->actingAs($cashier)->get('/intelligence/predictions')->assertStatus(403);
        $this->actingAs($cashier)->get('/intelligence/orders')->assertStatus(403);
        $this->actingAs($cashier)->get('/api/intelligence/predictions')->assertStatus(403);

        // Producto con stock crítico (< 3 días restantes según rotación de 30 días)
        $urgentProduct = $this->createProduct([
            'name' => 'Huevos AA x 30',
            'stock' => 3,
        ]);

        // Simular venta de 30 unidades en los últimos 30 días (1 unidad/día) -> Le quedan 3 días
        $date = Carbon::now()->subDays(5);
        $sale = Sale::create(['total' => 30000]);
        $sale->forceFill(['created_at' => $date])->save();
        $saleItem = new SaleItem([
            'sale_id' => $sale->id,
            'product_id' => $urgentProduct->id,
            'quantity' => 30,
            'price' => 1000,
            'cost_price' => 700,
        ]);
        $saleItem->forceFill(['created_at' => $date])->save();

        // Consultar vista de predicción como Admin
        $viewResponse = $this->actingAs($admin)->get('/intelligence/predictions');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Huevos AA x 30');

        // Consultar API JSON de predicciones
        $apiResponse = $this->actingAs($admin)->getJson('/api/intelligence/predictions');
        $apiResponse->assertStatus(200);
        $apiData = collect($apiResponse->json());

        $prediction = $apiData->firstWhere('name', 'Huevos AA x 30');
        $this->assertNotNull($prediction);
        $this->assertEquals(3, $prediction['stock']);
        $this->assertEquals(1.0, (float) $prediction['avg_daily']);
        $this->assertEquals(3.0, (float) $prediction['days_remaining']);

        // Consultar pantalla de pedidos sugeridos
        $ordersResponse = $this->actingAs($admin)->get('/intelligence/orders');
        $ordersResponse->assertStatus(200);
        $ordersResponse->assertSee('Pedido Sugerido');
    }

    /**
     * 6. FLUJO DE VENTAS POS: BÚSQUEDA, VENTA RÁPIDA, BLOQUEO PESIMISTA Y DEDUCCIÓN DE STOCK
     */
    public function test_pos_checkout_flow_atomic_locking_and_stock_deduction(): void
    {
        $cashier = User::factory()->create(['role' => User::ROLE_CASHIER]);
        $product = $this->createProduct([
            'name' => 'Chocolatina Jet 12g',
            'barcode' => '7702001002003',
            'purchase_price' => 600.00,
            'sale_price' => 1000.00,
            'stock' => 50,
        ]);

        // A. Búsqueda en POS por código de barras
        $searchByBarcode = $this->actingAs($cashier)->getJson('/pos/search?q=7702001002003');
        $searchByBarcode->assertStatus(200);
        $searchByBarcode->assertJsonFragment(['name' => 'Chocolatina Jet 12g']);

        // B. Búsqueda en POS por nombre
        $searchByName = $this->actingAs($cashier)->getJson('/pos/search?q=Chocolatina');
        $searchByName->assertStatus(200);
        $searchByName->assertJsonFragment(['barcode' => '7702001002003']);

        // C. Venta rápida exitosa en caja
        $sellResponse = $this->actingAs($cashier)->postJson('/pos/sell', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ]);

        $sellResponse->assertStatus(200);
        $sellResponse->assertJson([
            'success' => true,
            'total' => 10000.00,
            'message' => '¡Venta registrada!',
        ]);

        $product->refresh();
        $this->assertEquals(40, $product->stock);

        // Verificar que el item almacena el cost_price histórico inmutable
        $saleItem = SaleItem::where('product_id', $product->id)->first();
        $this->assertNotNull($saleItem);
        $this->assertEquals(600.00, (float) $saleItem->cost_price);
        $this->assertEquals(1000.00, (float) $saleItem->price);
        $this->assertEquals(10, $saleItem->quantity);

        // D. Intento de sobreventa bloqueado limpiamente
        $oversellResponse = $this->actingAs($cashier)->postJson('/pos/sell', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 999],
            ],
        ]);

        $oversellResponse->assertStatus(422);
        $oversellResponse->assertJsonStructure(['success', 'message']);

        // El stock permanece intacto en 40
        $product->refresh();
        $this->assertEquals(40, $product->stock);
    }

    /**
     * 7. FLUJO DE VENTAS CLÁSICAS, HISTORIAL Y VENTAS DE HOY
     */
    public function test_classic_sales_workflow_history_and_today_summary(): void
    {
        $cashier = User::factory()->create(['role' => User::ROLE_CASHIER]);
        $product = $this->createProduct([
            'name' => 'Pan Bimbo Blanco',
            'purchase_price' => 4000.00,
            'sale_price' => 6500.00,
            'stock' => 20,
        ]);

        // A. Formulario de creación clásico
        $this->actingAs($cashier)->get('/sales/create')->assertStatus(200);

        // B. Envío de venta
        $storeResponse = $this->actingAs($cashier)->post('/sales', [
            'products' => [
                ['id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $storeResponse->assertStatus(302);
        $storeResponse->assertRedirect(route('sales.create'));
        $storeResponse->assertSessionHas('success', 'Venta registrada exitosamente.');

        $product->refresh();
        $this->assertEquals(18, $product->stock);

        // C. Historial paginado de ventas
        $indexResponse = $this->actingAs($cashier)->get('/sales');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Pan Bimbo Blanco');

        // D. Ventas de Hoy
        $todayResponse = $this->actingAs($cashier)->get('/sales/today');
        $todayResponse->assertStatus(200);
        $todayResponse->assertSee('13.000'); // 2 * 6500
    }

    /**
     * 8. DETECCIÓN, CONSULTA Y DESCARTE DE ALERTAS DE VENCIMIENTO
     */
    public function test_expiration_alerts_workflow_detection_and_dismissal(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $product = $this->createProduct([
            'name' => 'Leche Deslactosada 1L',
            'stock' => 25,
            'expiration_date' => Carbon::today()->addDays(1),
        ]);

        // Ejecutar comando programado de detección de vencimientos
        $this->artisan('app:check-expirations')->assertSuccessful();

        $alert = ExpirationAlert::where('product_id', $product->id)->first();
        $this->assertNotNull($alert);
        $this->assertEquals('urgent', $alert->risk_level);
        $this->assertFalse($alert->isDismissed());

        // Consultar API de alertas urgentes para dashboard
        $apiResponse = $this->actingAs($admin)->getJson('/api/alerts/urgent');
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonFragment(['product_id' => $product->id]);

        // Pantalla de alertas
        $viewResponse = $this->actingAs($admin)->get('/alerts/expirations');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Leche Deslactosada 1L');

        // Descartar alerta individualmente (AJAX)
        $dismissResponse = $this->actingAs($admin)->postJson("/alerts/{$alert->id}/dismiss");
        $dismissResponse->assertStatus(200);
        $dismissResponse->assertJson(['success' => true, 'message' => 'Alerta descartada']);

        $alert->refresh();
        $this->assertTrue($alert->isDismissed());

        // Descartar todas las alertas de un producto (Redirect back)
        ExpirationAlert::create([
            'product_id' => $product->id,
            'days_remaining' => 2,
            'risk_level' => 'urgent',
            'stock_at_alert' => 25,
            'dismissed_at' => null,
        ]);

        $dismissAllResponse = $this->actingAs($admin)->from('/alerts/expirations')->post("/alerts/product/{$product->id}/dismiss-all");
        $dismissAllResponse->assertStatus(302);
        $dismissAllResponse->assertRedirect('/alerts/expirations');
        $dismissAllResponse->assertSessionHas('success', 'Alertas descartadas');
    }

    /**
     * 9. DASHBOARD GENERAL Y CONFIDENCIALIDAD DE MARGEN POR ROL
     */
    public function test_dashboard_kpis_and_financial_confidentiality_by_role(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $cashier = User::factory()->create(['role' => User::ROLE_CASHIER]);

        $product = $this->createProduct([
            'purchase_price' => 5000,
            'sale_price' => 8000,
            'stock' => 50,
        ]);

        // Registrar una venta hoy
        $sale = Sale::create(['total' => 16000]);
        $saleItem = new SaleItem([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 8000,
            'cost_price' => 5000,
        ]);
        $saleItem->save();

        // Admin: puede ver la ganancia estimada del día ($6,000)
        $adminDashboard = $this->actingAs($admin)->get('/dashboard');
        $adminDashboard->assertStatus(200);
        $adminDashboard->assertViewHas('profitToday', 6000.0);

        // Cajero: la ganancia se mantiene estrictamente en 0 para proteger datos financieros
        $cashierDashboard = $this->actingAs($cashier)->get('/dashboard');
        $cashierDashboard->assertStatus(200);
        $cashierDashboard->assertViewHas('profitToday', 0);
    }

    /**
     * 10. ACCESO PÚBLICO: PÁGINA OFFLINE (PWA) Y POLÍTICA DE PRIVACIDAD (GDPR)
     */
    public function test_offline_page_and_privacy_policy_are_publicly_accessible(): void
    {
        // PWA offline screen
        $this->get('/offline')->assertStatus(200);

        // GDPR / Habeas Data privacy policy
        $this->get('/privacy-policy')->assertStatus(200);
    }
}
