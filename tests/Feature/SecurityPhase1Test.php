<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityPhase1Test extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_access_financial_profits_report(): void
    {
        $cashier = User::factory()->cashier()->create();

        $response = $this->actingAs($cashier)->get(route('reports.profits'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_financial_profits_report(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('reports.profits'));

        $response->assertStatus(200);
    }

    public function test_cashier_cannot_create_or_modify_products(): void
    {
        $cashier = User::factory()->cashier()->create();

        $responseCreate = $this->actingAs($cashier)->get(route('products.create'));
        $responseCreate->assertStatus(403);

        $responseStore = $this->actingAs($cashier)->post(route('products.store'), [
            'name' => 'Producto Hacker',
            'purchase_price' => 100,
            'sale_price' => 150,
            'stock' => 10,
        ]);
        $responseStore->assertStatus(403);
    }

    public function test_cashier_can_access_pos_and_sell(): void
    {
        $cashier = User::factory()->cashier()->create();
        $product = Product::create([
            'name' => 'Arroz 1kg',
            'purchase_price' => 2000,
            'sale_price' => 2500,
            'stock' => 10,
        ]);

        $response = $this->actingAs($cashier)->get(route('pos'));
        $response->assertStatus(200);

        // Venta rápida POS con suficiente stock
        $sellResponse = $this->actingAs($cashier)->postJson(route('pos.sell'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $sellResponse->assertStatus(200);
        $sellResponse->assertJson(['success' => true]);

        $this->assertEquals(8, $product->fresh()->stock);
    }

    public function test_overselling_is_blocked_and_reverts_cleanly(): void
    {
        $cashier = User::factory()->cashier()->create();
        $product = Product::create([
            'name' => 'Aceite 1L',
            'purchase_price' => 5000,
            'sale_price' => 6500,
            'stock' => 2,
        ]);

        // Intentar vender más unidades de las disponibles
        $response = $this->actingAs($cashier)->postJson(route('pos.sell'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        // El stock debe permanecer intacto
        $this->assertEquals(2, $product->fresh()->stock);
    }
}
