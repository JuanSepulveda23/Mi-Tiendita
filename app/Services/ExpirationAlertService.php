<?php

namespace App\Services;

use App\Models\ExpirationAlert;
use App\Models\Product;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ExpirationAlertService
{
    /**
     * Revisar productos próximos a vencer o vencidos y generar/actualizar alertas activas.
     */
    public function checkAndGenerateAlerts(): int
    {
        $products = Product::whereNotNull('expiration_date')
            ->where('stock', '>', 0)
            ->get();

        $alertsCreated = 0;

        foreach ($products as $product) {
            $daysRemaining = $product->daysUntilExpiration();

            if ($daysRemaining <= 3 && $daysRemaining >= 0) {
                $avgDaily = $this->getAverageDailySales($product->id);
                $rotationDays = $avgDaily > 0 ? round($product->stock / $avgDaily, 1) : null;
                $willExpire = $rotationDays !== null && $rotationDays > $daysRemaining;
                $riskLevel = $daysRemaining <= 1 ? 'urgent' : ($daysRemaining <= 2 ? 'high' : 'low');

                $existingAlert = ExpirationAlert::where('product_id', $product->id)
                    ->active()
                    ->first();

                if (! $existingAlert) {
                    ExpirationAlert::create([
                        'product_id' => $product->id,
                        'days_remaining' => $daysRemaining,
                        'risk_level' => $riskLevel,
                        'stock_at_alert' => $product->stock,
                        'avg_daily_sales' => $avgDaily,
                        'rotation_days' => $rotationDays,
                        'will_expire' => $willExpire,
                    ]);

                    $alertsCreated++;
                } else {
                    $existingAlert->update([
                        'days_remaining' => $daysRemaining,
                        'risk_level' => $riskLevel,
                        'will_expire' => $willExpire,
                    ]);
                }
            } elseif ($daysRemaining < 0) {
                // Producto vencido: verificar si ya existe para evitar duplicados
                $existingAlert = ExpirationAlert::where('product_id', $product->id)
                    ->active()
                    ->first();

                if (! $existingAlert) {
                    ExpirationAlert::create([
                        'product_id' => $product->id,
                        'days_remaining' => $daysRemaining,
                        'risk_level' => 'urgent',
                        'stock_at_alert' => $product->stock,
                        'avg_daily_sales' => $this->getAverageDailySales($product->id),
                        'rotation_days' => 0,
                        'will_expire' => true,
                    ]);

                    $alertsCreated++;
                } else {
                    $existingAlert->update([
                        'days_remaining' => $daysRemaining,
                        'risk_level' => 'urgent',
                        'stock_at_alert' => $product->stock,
                        'will_expire' => true,
                    ]);
                }
            }
        }

        return $alertsCreated;
    }

    /**
     * Obtener alertas paginadas ordenadas por riesgo y días restantes.
     */
    public function getAlertsPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return ExpirationAlert::with('product')
            ->active()
            ->orderByRaw("CASE risk_level WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 ELSE 3 END")
            ->orderBy('days_remaining')
            ->paginate($perPage);
    }

    /**
     * Estadísticas consolidadas de alertas activas.
     *
     * @return array{total: int, urgent: int, high: int, will_expire: int}
     */
    public function getActiveAlertsStats(): array
    {
        return [
            'total' => ExpirationAlert::active()->count(),
            'urgent' => ExpirationAlert::active()->urgent()->count(),
            'high' => ExpirationAlert::active()->highRisk()->count(),
            'will_expire' => ExpirationAlert::active()->willExpire()->count(),
        ];
    }

    /**
     * Alertas urgentes activas para widgets.
     */
    public function getActiveUrgentAlerts(int $limit = 5): Collection
    {
        return ExpirationAlert::with('product')
            ->active()
            ->urgent()
            ->orderBy('days_remaining')
            ->take($limit)
            ->get();
    }

    public function dismissAlert(ExpirationAlert $alert): void
    {
        $alert->dismiss();
    }

    public function dismissProductAlerts(int $productId): void
    {
        ExpirationAlert::where('product_id', $productId)
            ->active()
            ->each(fn ($alert) => $alert->dismiss());
    }

    private function getAverageDailySales(int $productId): float
    {
        $since = Carbon::now()->subDays(7)->startOfDay();

        $totalSold = SaleItem::where('product_id', $productId)
            ->where('created_at', '>=', $since)
            ->sum('quantity');

        return $totalSold > 0 ? round($totalSold / 7, 2) : 0;
    }
}

