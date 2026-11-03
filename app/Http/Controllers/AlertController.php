<?php

namespace App\Http\Controllers;

use App\Models\ExpirationAlert;
use App\Services\ExpirationAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function __construct(
        protected ExpirationAlertService $alertService
    ) {}

    /**
     * Ver todas las alertas de vencimiento.
     */
    public function expirations(): View
    {
        $alerts = $this->alertService->getAlertsPaginated(20);
        $stats = $this->alertService->getActiveAlertsStats();

        return view('alerts.expirations', compact('alerts', 'stats'));
    }

    /**
     * Descartar una alerta.
     */
    public function dismiss(ExpirationAlert $alert): JsonResponse
    {
        $this->alertService->dismissAlert($alert);

        return response()->json([
            'success' => true,
            'message' => 'Alerta descartada',
        ]);
    }

    /**
     * Descartar todas las alertas de un producto.
     */
    public function dismissProduct(int $productId): RedirectResponse
    {
        $this->alertService->dismissProductAlerts($productId);

        return back()->with('success', 'Alertas descartadas');
    }

    /**
     * API: obtener alertas activas (para widget en dashboard).
     */
    public function activeAlertsApi(): JsonResponse
    {
        $alerts = $this->alertService->getActiveUrgentAlerts(5);

        return response()->json($alerts);
    }
}
