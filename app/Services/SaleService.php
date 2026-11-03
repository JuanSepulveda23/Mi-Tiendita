<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * Registrar una venta clásica a partir del formulario legacy.
     *
     * @param  array<int, array{id: int, quantity: int}>  $productsData
     *
     * @throws DomainException
     */
    public function createSale(array $productsData): Sale
    {
        return DB::transaction(function () use ($productsData) {
            $sale = Sale::create(['total' => 0]);
            $total = 0;

            foreach ($productsData as $item) {
                $product = Product::where('id', $item['id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->stock < $item['quantity']) {
                    throw new DomainException("Stock insuficiente para {$product->name}. Stock disponible: {$product->stock}.");
                }

                $subtotal = $product->sale_price * $item['quantity'];

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->sale_price,
                    'cost_price' => $product->purchase_price,
                ]);

                $product->decrement('stock', $item['quantity']);
                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });
    }

    /**
     * Registrar una venta rápida desde el Punto de Venta (POS).
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $itemsData
     *
     * @throws DomainException
     */
    public function createQuickSale(array $itemsData): Sale
    {
        return DB::transaction(function () use ($itemsData) {
            $sale = Sale::create(['total' => 0]);
            $total = 0;

            foreach ($itemsData as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->stock < $item['quantity']) {
                    throw new DomainException("Stock insuficiente para {$product->name}. Stock actual: {$product->stock}.");
                }

                $subtotal = $product->sale_price * $item['quantity'];

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->sale_price,
                    'cost_price' => $product->purchase_price,
                ]);

                $product->decrement('stock', $item['quantity']);
                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });
    }

    /**
     * Obtener resumen de ventas del día actual.
     *
     * @return array{sales: Collection, totalToday: float, salesCountToday: int, productsSoldToday: int, profitToday: float}
     */
    public function getTodaySummary(?User $user = null): array
    {
        $today = Carbon::today();

        $sales = Sale::with('items.product')
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->get();

        $totalToday = (float) $sales->sum('total');
        $salesCountToday = $sales->count();
        $productsSoldToday = (int) $sales->flatMap->items->sum('quantity');

        $profitToday = ($user && $user->isAdmin())
            ? (float) (SaleItem::whereHas('sale', fn ($q) => $q->whereDate('created_at', $today))
                ->selectRaw('SUM(quantity * (price - cost_price)) as profit')
                ->value('profit') ?? 0)
            : 0.0;

        return compact('sales', 'totalToday', 'salesCountToday', 'productsSoldToday', 'profitToday');
    }

    /**
     * Buscar productos disponibles para el POS por nombre o código de barras.
     */
    public function searchProducts(string $query, int $limit = 20): Collection
    {
        return Product::where('stock', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->take($limit)
            ->get(['id', 'name', 'sale_price', 'stock', 'barcode', 'is_frequent']);
    }
}
