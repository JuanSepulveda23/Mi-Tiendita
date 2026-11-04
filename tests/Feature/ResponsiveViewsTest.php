<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsiveViewsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Responsive',
            'email' => 'admin@responsive.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->cashier = User::create([
            'name' => 'Cashier Responsive',
            'email' => 'cashier@responsive.com',
            'password' => bcrypt('password123'),
            'role' => 'cashier',
        ]);
    }

    public function test_dashboard_renders_with_responsive_shell_and_grids(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('sidebar-mobile-toggle');
        $response->assertSee('grid-cols-1 sm:grid-cols-2 lg:grid-cols-4', false);
    }

    public function test_pos_renders_with_mobile_tab_switcher_and_floating_cart_bar(): void
    {
        Product::create([
            'name' => 'Arroz Diana 1kg',
            'sale_price' => 4500,
            'purchase_price' => 3200,
            'stock' => 20,
            'min_stock' => 5,
            'is_frequent' => true,
        ]);

        $response = $this->actingAs($this->cashier)->get(route('pos'));

        $response->assertStatus(200);
        $response->assertSee('pos-mobile-tabs');
        $response->assertSee('tab-btn-products');
        $response->assertSee('tab-btn-cart');
        $response->assertSee('pos-mobile-cart-bar');
    }

    public function test_products_index_renders_with_responsive_table_and_actions(): void
    {
        Product::create([
            'name' => 'Leche Alquería 1L',
            'sale_price' => 3800,
            'purchase_price' => 2900,
            'stock' => 15,
            'min_stock' => 4,
            'is_frequent' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('overflow-x-auto');
        $response->assertSee('+ Nuevo Producto');
    }

    public function test_reports_profits_renders_with_responsive_filter_and_kpis(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.profits'));

        $response->assertStatus(200);
        $response->assertSee('days-filter');
        $response->assertSee('grid-cols-1 sm:grid-cols-2 lg:grid-cols-4', false);
    }
}
