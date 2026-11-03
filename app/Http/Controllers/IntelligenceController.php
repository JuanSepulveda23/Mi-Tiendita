<?php

namespace App\Http\Controllers;

use App\Services\InventoryIntelligenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class IntelligenceController extends Controller
{
    public function __construct(
        protected InventoryIntelligenceService $intelligenceService
    ) {}

    /**
     * Predicción de compras: muestra cuántos días de stock quedan por producto.
     */
    public function predictions(): View
    {
        $products = $this->intelligenceService->getPredictions(30);

        return view('intelligence.predictions', compact('products'));
    }

    /**
     * Lista automática de pedidos sugeridos basada en rotación.
     */
    public function orders(): View
    {
        $data = $this->intelligenceService->getSuggestedOrders(7, 2);

        return view('intelligence.orders', $data);
    }

    /**
     * API: datos para predicción (JSON).
     */
    public function predictionsApi(): JsonResponse
    {
        $products = $this->intelligenceService->getPredictions(30)->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'stock' => $p->stock,
            'avg_daily' => $p->avg_daily_sales,
            'days_remaining' => $p->days_remaining,
        ]);

        return response()->json($products);
    }
}
