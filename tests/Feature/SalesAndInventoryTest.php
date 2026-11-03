<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesAndInventoryTest extends TestCase
{
    use RefreshDatabase;

    /* ══════════════════════════════════════════════════════════════════
     |  MÓDULO DE PRODUCTOS E INVENTARIO
     | ══════════════════════════════════════════════════════════════════ */

    public function test_users_can_view_products_catalog(): void
    {
        $cashier = User::factory()->cashier()->create();
        Product::create([
            'name' => 'Arroz 1kg',
            'purchase_price' => 2000,
            'sale_price' => 2500,
            'stock' => 10,
        ]);

        $response = $this->actingAs($cashier)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Arroz 1kg');
    }

    public function test_admin_can_create_product_with_complete_inventory_data(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'Gaseosa Manzana 1.5L',
            'barcode' => '7701234567890',
            'purchase_price' => 3000,
            'sale_price' => 4200,
            'stock' => 24,
            'min_stock' => 5,
            'category' => 'Bebidas',
            'supplier' => 'Postobón',
            'is_frequent' => 1,
            'entry_date' => '2026-11-01',
            'expiration_date' => '2026-12-15',
        ]);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Gaseosa Manzana 1.5L',
            'barcode' => '7701234567890',
            'stock' => 24,
            'is_frequent' => 1,
            'category' => 'Bebidas',
            'supplier' => 'Postobón',
        ]);
    }

    public function test_admin_can_update_product_inventory_and_pricing(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create([
            'name' => 'Atún en Agua',
            'purchase_price' => 4500,
            'sale_price' => 6000,
            'stock' => 15,
        ]);

        $response = $this->actingAs($admin)->put(route('products.update', $product), [
            'name' => 'Atún en Agua 170g',
            'purchase_price' => 4800,
            'sale_price' => 6500,
            'stock' => 30,
            'min_stock' => 8,
            'category' => 'Enlatados',
            'supplier' => 'Van Camps',
        ]);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Atún en Agua 170g',
            'sale_price' => 6500,
            'stock' => 30,
        ]);
    }

    public function test_admin_can_delete_product_but_cashier_is_forbidden(): void
    {
        $admin = User::factory()->admin()->create();
        $cashier = User::factory()->cashier()->create();

        $product = Product::create([
            'name' => 'Producto Obsoleto',
            'purchase_price' => 100,
            'sale_price' => 200,
            'stock' => 0,
        ]);

        // Cajero intenta eliminar: 403 Forbidden
        $responseCashier = $this->actingAs($cashier)->delete(route('products.destroy', $product));
        $responseCashier->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $product->id]);

        // Administrador elimina: Redirección exitosa y borrado
        $responseAdmin = $this->actingAs($admin)->delete(route('products.destroy', $product));
        $responseAdmin->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_can_toggle_frequent_product_status(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create([
            'name' => 'Pan Casero',
            'purchase_price' => 500,
            'sale_price' => 800,
            'stock' => 10,
            'is_frequent' => false,
        ]);

        $response = $this->actingAs($admin)->postJson(route('products.toggleFrequent', $product));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_frequent' => true,
        ]);

        $this->assertTrue($product->fresh()->is_frequent);
    }

    public function test_admin_can_quick_store_product_via_ajax(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson(route('products.quickStore'), [
            'name' => 'Chicle Menta',
            'sale_price' => 300,
            'stock' => 50,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'product' => [
                'name' => 'Chicle Menta',
                'sale_price' => 300,
                'stock' => 50,
            ],
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Chicle Menta',
            'stock' => 50,
        ]);
    }

    /* ══════════════════════════════════════════════════════════════════
     |  BÚSQUEDA Y MÓDULO DE VENTAS (POS Y CLÁSICO)
     | ══════════════════════════════════════════════════════════════════ */

    public function test_pos_search_endpoint_returns_only_matching_in_stock_products(): void
    {
        $cashier = User::factory()->cashier()->create();

        // Producto disponible por nombre
        Product::create(['name' => 'Jabón Rey', 'barcode' => '111', 'purchase_price' => 1000, 'sale_price' => 1500, 'stock' => 5]);
        // Producto disponible por código de barras
        Product::create(['name' => 'Detergente Ariel', 'barcode' => '770999888', 'purchase_price' => 4000, 'sale_price' => 5500, 'stock' => 3]);
        // Producto agotado (no debe aparecer)
        Product::create(['name' => 'Jabón Azul', 'barcode' => '222', 'purchase_price' => 1000, 'sale_price' => 1500, 'stock' => 0]);

        // Buscar por nombre
        $responseName = $this->actingAs($cashier)->getJson(route('pos.search', ['q' => 'Jabón']));
        $responseName->assertStatus(200);
        $responseName->assertJsonCount(1);
        $responseName->assertJsonFragment(['name' => 'Jabón Rey']);

        // Buscar por código de barras
        $responseBarcode = $this->actingAs($cashier)->getJson(route('pos.search', ['q' => '770999888']));
        $responseBarcode->assertStatus(200);
        $responseBarcode->assertJsonCount(1);
        $responseBarcode->assertJsonFragment(['name' => 'Detergente Ariel']);
    }

    public function test_classic_sale_creates_sale_and_decrements_inventory(): void
    {
        $cashier = User::factory()->cashier()->create();
        $p1 = Product::create(['name' => 'Huevos AA x12', 'purchase_price' => 7000, 'sale_price' => 9000, 'stock' => 10]);
        $p2 = Product::create(['name' => 'Panal de Huevos', 'purchase_price' => 15000, 'sale_price' => 19000, 'stock' => 5]);

        $response = $this->actingAs($cashier)->post(route('sales.store'), [
            'products' => [
                ['id' => $p1->id, 'quantity' => 2],
                ['id' => $p2->id, 'quantity' => 1],
            ],
        ]);

        $response->assertRedirect(route('sales.create'));
        $response->assertSessionHas('success');

        // Total esperado: (9000 * 2) + (19000 * 1) = 37000
        $this->assertDatabaseHas('sales', ['total' => 37000]);

        // Verificar items con precios y costos congelados
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $p1->id,
            'quantity' => 2,
            'price' => 9000,
            'cost_price' => 7000,
        ]);

        // Verificar decremento de stock
        $this->assertEquals(8, $p1->fresh()->stock);
        $this->assertEquals(4, $p2->fresh()->stock);
    }

    public function test_pos_quick_sale_checkout_flow(): void
    {
        $cashier = User::factory()->cashier()->create();
        $product = Product::create(['name' => 'Chocolate Sol', 'purchase_price' => 2200, 'sale_price' => 3000, 'stock' => 12]);

        $response = $this->actingAs($cashier)->postJson(route('pos.sell'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 12000,
        ]);

        $this->assertEquals(8, $product->fresh()->stock);
    }

    public function test_pos_sale_fails_gracefully_when_stock_insufficient(): void
    {
        $cashier = User::factory()->cashier()->create();
        $product = Product::create(['name' => 'Cerveza Club', 'purchase_price' => 2500, 'sale_price' => 3500, 'stock' => 1]);

        $response = $this->actingAs($cashier)->postJson(route('pos.sell'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        // El stock no debe alterarse
        $this->assertEquals(1, $product->fresh()->stock);
    }

    public function test_sales_today_summary_visibility_by_role(): void
    {
        $cashier = User::factory()->cashier()->create();
        $admin = User::factory()->admin()->create();

        $product = Product::create(['name' => 'Agua 600ml', 'purchase_price' => 1000, 'sale_price' => 1500, 'stock' => 20]);

        $this->actingAs($cashier)->postJson(route('pos.sell'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ]);

        // Cajero ve ventas del día pero ganancia en 0 (oculta)
        $cashierView = $this->actingAs($cashier)->get(route('sales.today'));
        $cashierView->assertStatus(200);
        $cashierView->assertViewHas('totalToday', 6000.0);
        $cashierView->assertViewHas('productsSoldToday', 4);
        $cashierView->assertViewHas('profitToday', 0.0);

        // Administrador ve la ganancia real calculada: 4 * (1500 - 1000) = 2000
        $adminView = $this->actingAs($admin)->get(route('sales.today'));
        $adminView->assertStatus(200);
        $adminView->assertViewHas('profitToday', 2000.0);
    }
}
