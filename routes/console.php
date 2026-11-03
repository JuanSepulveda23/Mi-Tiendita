<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Verificación diaria de productos en riesgo de vencimiento o expirados
Schedule::command('app:check-expirations')
    ->dailyAt('00:01')
    ->withoutOverlapping();
