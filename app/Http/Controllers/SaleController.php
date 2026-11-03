<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {}

    /**
     * Historial paginado de ventas.
     */
    public function index(): View
    {
        $sales = Sale::with('items.product')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('sales.index', compact('sales'));
    }

    /**
     * Formulario clásico de venta (legacy).
     */
    public function create(): View
    {
        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('sales.create', compact('products'));
    }

    /**
     * Modo Caja — POS completo.
     */
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

    /**
     * API: Buscar productos en tiempo real para el POS.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $products = $this->saleService->searchProducts(
            $request->input('q', '')
        );

        return response()->json($products);
    }

    /**
     * Guardar venta desde formulario clásico.
     */
    public function store(StoreSaleRequest $request): RedirectResponse
    {
        try {
            $this->saleService->createSale($request->validated()['products']);
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['stock' => $e->getMessage()]);
        }

        return redirect()
            ->route('sales.create')
            ->with('success', 'Venta registrada exitosamente.');
    }

    /**
     * API: Guardar venta rápida desde Modo Caja (POS).
     */
    public function storeQuick(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $sale = $this->saleService->createQuickSale($request->input('items'));

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'total' => $sale->total,
                'message' => '¡Venta registrada!',
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error inesperado al procesar la venta.',
            ], 500);
        }
    }

    /**
     * Historial y resumen del día actual.
     */
    public function today(): View
    {
        $summary = $this->saleService->getTodaySummary(request()->user());

        return view('sales.today', $summary);
    }
}
