<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleController extends Controller
{
    /* ────────────────────────────────────────
     |  Historial de ventas
     |──────────────────────────────────────── */
    public function index(): View
    {
        $sales = Sale::with('items.product')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('sales.index', compact('sales'));
    }

    /* ────────────────────────────────────────
     |  Formulario clásico de venta (legacy)
     |──────────────────────────────────────── */
    public function create(): View
    {
        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('sales.create', compact('products'));
    }

    /* ────────────────────────────────────────
     |  MODO CAJA — POS completo
     |──────────────────────────────────────── */
    public function pos(): View
    {
        $frequentProducts = Product::frequent()
            ->where('stock', '>', 0)
            ->get();

        $allProducts = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('sales.pos', compact('frequentProducts', 'allProducts'));
    }

    /* ────────────────────────────────────────
     |  API: Buscar productos (para POS)
     |──────────────────────────────────────── */
    public function searchProducts(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $products = Product::where('stock', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->take(20)
            ->get(['id', 'name', 'sale_price', 'stock', 'barcode', 'is_frequent']);

        return response()->json($products);
    }

    /* ────────────────────────────────────────
     |  Guardar venta (formulario clásico)
     |──────────────────────────────────────── */
    public function store(StoreSaleRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $sale = Sale::create(['total' => 0]);
            $total = 0;

            foreach ($request->validated()['products'] as $item) {
                $product = Product::findOrFail($item['id']);

                if ($product->stock < $item['quantity']) {
                    abort(422, "Stock insuficiente para {$product->name}.");
                }

                $subtotal = $product->sale_price * $item['quantity'];

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $product->sale_price,
                ]);

                $product->decrement('stock', $item['quantity']);

                $total += $subtotal;
            }

            $sale->update(['total' => $total]);
        });

        return redirect()
            ->route('sales.create')
            ->with('success', 'Venta registrada exitosamente.');
    }

    /* ────────────────────────────────────────
     |  API: Guardar venta rápida (POS)
     |──────────────────────────────────────── */
    public function storeQuick(Request $request): JsonResponse
    {
        $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $sale = null;

        DB::transaction(function () use ($request, &$sale) {
            $sale = Sale::create(['total' => 0]);
            $total = 0;

            foreach ($request->input('items') as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    abort(422, "Stock insuficiente para {$product->name}.");
                }

                $subtotal = $product->sale_price * $item['quantity'];

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $product->sale_price,
                ]);

                $product->decrement('stock', $item['quantity']);
                $total += $subtotal;
            }

            $sale->update(['total' => $total]);
        });

        return response()->json([
            'success'  => true,
            'sale_id'  => $sale->id,
            'total'    => $sale->total,
            'message'  => '¡Venta registrada!',
        ]);
    }

    /* ────────────────────────────────────────
     |  Historial del día (resumen)
     |──────────────────────────────────────── */
    public function today(): View
    {
        $today = Carbon::today();

        $sales = Sale::with('items.product')
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->get();

        $totalToday        = $sales->sum('total');
        $salesCountToday   = $sales->count();
        $productsSoldToday = $sales->flatMap->items->sum('quantity');

        $profitToday = SaleItem::whereHas('sale', fn ($q) => $q->whereDate('created_at', $today))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(sale_items.quantity * (sale_items.price - products.purchase_price)) as profit')
            ->value('profit') ?? 0;

        return view('sales.today', compact(
            'sales',
            'totalToday',
            'salesCountToday',
            'productsSoldToday',
            'profitToday'
        ));
    }
}
