<?php

namespace Tests\Feature;

use App\Models\ExpirationAlert;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DataIntegrityPhase2Test extends TestCase
{
    use RefreshDatabase;

    public function test_product_expiration_and_entry_dates_can_be_stored(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'Leche Entera 1L',
            'purchase_price' => 2500,
            'sale_price' => 3500,
            'stock' => 15,
            'entry_date' => '2026-11-01',
            'expiration_date' => '2026-11-15',
        ]);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Leche Entera 1L',
            'entry_date' => '2026-11-01 00:00:00',
            'expiration_date' => '2026-11-15 00:00:00',
        ]);
    }

    public function test_product_expiration_and_entry_dates_can_be_updated(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create([
            'name' => 'Yogurt Griego',
            'purchase_price' => 3000,
            'sale_price' => 4500,
            'stock' => 20,
            'entry_date' => '2026-11-01',
            'expiration_date' => '2026-11-20',
        ]);

        $response = $this->actingAs($admin)->put(route('products.update', $product), [
            'name' => 'Yogurt Griego 500g',
            'purchase_price' => 3200,
            'sale_price' => 4800,
            'stock' => 20,
            'entry_date' => '2026-11-02',
            'expiration_date' => '2026-11-25',
        ]);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Yogurt Griego 500g',
            'entry_date' => '2026-11-02 00:00:00',
            'expiration_date' => '2026-11-25 00:00:00',
        ]);
    }

    public function test_sale_item_freezes_cost_price_at_moment_of_sale(): void
    {
        $cashier = User::factory()->cashier()->create();
        $product = Product::create([
            'name' => 'Galletas Chocolate',
            'purchase_price' => 800,
            'sale_price' => 1200,
            'stock' => 10,
        ]);

        // Registrar venta rápida POS
        $response = $this->actingAs($cashier)->postJson(route('pos.sell'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ]);

        $response->assertStatus(200);

        $saleItem = SaleItem::where('product_id', $product->id)->firstOrFail();
        $this->assertEquals(800.00, (float) $saleItem->cost_price);
        $this->assertEquals(1200.00, (float) $saleItem->price);
        $this->assertEquals(3, $saleItem->quantity);
        $this->assertEquals(1200.00, (float) $saleItem->profit()); // (1200 - 800) * 3 = 1200
    }

    public function test_changing_product_purchase_price_does_not_mutate_past_sale_cost(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create([
            'name' => 'Café Tostado',
            'purchase_price' => 5000,
            'sale_price' => 7000,
            'stock' => 10,
        ]);

        // Venta realizada cuando el costo era 5000
        $this->actingAs($admin)->postJson(route('pos.sell'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        // La ganancia original es (7000 - 5000) * 2 = 4000
        $saleItem = SaleItem::where('product_id', $product->id)->firstOrFail();
        $this->assertEquals(5000.00, (float) $saleItem->cost_price);

        // Proveedor sube el costo a 6500 días después
        $product->update(['purchase_price' => 6500]);

        // El costo de la venta histórica debe permanecer intacto en 5000
        $this->assertEquals(5000.00, (float) $saleItem->fresh()->cost_price);

        // Verificamos reporte de ganancias del día
        $todayResponse = $this->actingAs($admin)->get(route('sales.today'));
        $todayResponse->assertStatus(200);
        $todayResponse->assertViewHas('profitToday', 4000.0);
    }

    public function test_check_expirations_command_is_idempotent_for_expired_products(): void
    {
        // Producto vencido hace 2 días
        $expiredProduct = Product::create([
            'name' => 'Pan Tajado Vencido',
            'purchase_price' => 1500,
            'sale_price' => 2500,
            'stock' => 5,
            'expiration_date' => Carbon::today()->subDays(2),
        ]);

        // Ejecutar comando primera vez
        Artisan::call('app:check-expirations');

        $this->assertEquals(1, ExpirationAlert::where('product_id', $expiredProduct->id)->active()->count());

        // Ejecutar comando segunda vez (mismo día o corrida repetida)
        Artisan::call('app:check-expirations');

        // NO debe haber duplicados en alertas activas
        $this->assertEquals(1, ExpirationAlert::where('product_id', $expiredProduct->id)->active()->count());
    }
}
