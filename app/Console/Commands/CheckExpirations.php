<?php

namespace App\Console\Commands;

use App\Models\ExpirationAlert;
use App\Models\Product;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpirations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-expirations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check expiration dates daily and create alerts for products at risk';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Buscar productos con fecha de vencimiento
        $products = Product::whereNotNull('expiration_date')
            ->where('stock', '>', 0)
            ->get();

        $alertsCreated = 0;

        foreach ($products as $product) {
            $daysRemaining = $product->daysUntilExpiration();

            // Solo alertar si faltan 3 días o menos
            if ($daysRemaining <= 3 && $daysRemaining >= 0) {
                // Calcular rotación: ¿se venderá todo antes del vencimiento?
                $avgDaily = $this->getAverageDailySales($product->id);
                $rotationDays = $avgDaily > 0 ? round($product->stock / $avgDaily, 1) : null;
                $willExpire = $rotationDays !== null && $rotationDays > $daysRemaining;

                // Determinar nivel de riesgo
                $riskLevel = $daysRemaining <= 1 ? 'urgent' : ($daysRemaining <= 2 ? 'high' : 'low');

                // Verificar si ya existe una alerta activa para este producto
                $existingAlert = ExpirationAlert::where('product_id', $product->id)
                    ->active()
                    ->first();

                if (!$existingAlert) {
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

                    $this->info(sprintf(
                        '⚠️  Alerta creada para %s (vence en %d días, stock: %d)',
                        $product->name,
                        $daysRemaining,
                        $product->stock
                    ));

                    if ($willExpire) {
                        $this->warn(sprintf(
                            '   🚨 RIESGO DE PÉRDIDA: Se vencerá en %d días pero el stock dura %s días',
                            $daysRemaining,
                            $rotationDays
                        ));
                    }
                } else {
                    // Actualizar alerta existente
                    $existingAlert->update([
                        'days_remaining' => $daysRemaining,
                        'risk_level' => $riskLevel,
                        'will_expire' => $willExpire,
                    ]);
                }
            } elseif ($daysRemaining < 0) {
                // Producto vencido: crear alerta de urgencia máxima
                ExpirationAlert::create([
                    'product_id' => $product->id,
                    'days_remaining' => $daysRemaining,
                    'risk_level' => 'urgent',
                    'stock_at_alert' => $product->stock,
                    'will_expire' => true,
                ]);

                $this->error(sprintf(
                    '🛑 PRODUCTO VENCIDO: %s (vencido hace %d días)',
                    $product->name,
                    abs($daysRemaining)
                ));
            }
        }

        $this->info(sprintf('✅ Chequeo completado. %d alerta(s) creada(s).', $alertsCreated));

        return 0;
    }

    /**
     * Obtener promedio de ventas diarias de los últimos 7 días.
     */
    private function getAverageDailySales(int $productId): float
    {
        $since = Carbon::now()->subDays(7)->startOfDay();

        $totalSold = SaleItem::where('product_id', $productId)
            ->where('created_at', '>=', $since)
            ->sum('quantity');

        return $totalSold > 0 ? round($totalSold / 7, 2) : 0;
    }
}
