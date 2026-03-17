<?php

namespace App\Http\Controllers;

use App\Models\ExpirationAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AlertController extends Controller
{
    /**
     * Ver todas las alertas de vencimiento.
     */
    public function expirations(): View
    {
        $alerts = ExpirationAlert::with('product')
            ->active()
            ->orderByRaw("FIELD(risk_level, 'urgent', 'high', 'low')")
            ->orderBy('days_remaining')
            ->paginate(20);

        $stats = [
            'total' => ExpirationAlert::active()->count(),
            'urgent' => ExpirationAlert::active()->urgent()->count(),
            'high' => ExpirationAlert::active()->highRisk()->count(),
            'will_expire' => ExpirationAlert::active()->willExpire()->count(),
        ];

        return view('alerts.expirations', compact('alerts', 'stats'));
    }

    /**
     * Descartar una alerta.
     */
    public function dismiss(ExpirationAlert $alert): JsonResponse
    {
        $alert->dismiss();

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
        ExpirationAlert::where('product_id', $productId)
            ->active()
            ->each(fn ($alert) => $alert->dismiss());

        return back()->with('success', 'Alertas descartadas');
    }

    /**
     * API: obtener alertas activas (para widget en dashboard).
     */
    public function activeAlertsApi(): JsonResponse
    {
        $alerts = ExpirationAlert::with('product')
            ->active()
            ->urgent()
            ->orderBy('days_remaining')
            ->take(5)
            ->get();

        return response()->json($alerts);
    }
}
