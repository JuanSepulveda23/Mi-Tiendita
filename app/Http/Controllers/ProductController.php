<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::orderBy('name')->paginate(15);

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }

    /* ────────────────────────────────────────
     |  Toggle producto frecuente (AJAX)
     |──────────────────────────────────────── */
    public function toggleFrequent(Product $product): JsonResponse
    {
        $product->update(['is_frequent' => !$product->is_frequent]);

        return response()->json([
            'success'     => true,
            'is_frequent' => $product->is_frequent,
        ]);
    }

    /* ────────────────────────────────────────
     |  Registro rápido (solo nombre, precio, stock)
     |──────────────────────────────────────── */
    public function quickStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'stock'      => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::create([
            'name'           => $validated['name'],
            'sale_price'     => $validated['sale_price'],
            'purchase_price' => 0,
            'stock'          => $validated['stock'],
        ]);

        return response()->json([
            'success' => true,
            'product' => $product,
            'message' => 'Producto creado exitosamente.',
        ]);
    }
}
