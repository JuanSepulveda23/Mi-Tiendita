<?php

namespace App\Console\Commands;

use App\Services\ExpirationAlertService;
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
    public function handle(ExpirationAlertService $alertService): int
    {
        $this->info('Iniciando chequeo de vencimientos...');

        $alertsCreated = $alertService->checkAndGenerateAlerts();

        $this->info(sprintf('Chequeo completado. %d alerta(s) nueva(s) creada(s).', $alertsCreated));

        return 0;
    }
}
