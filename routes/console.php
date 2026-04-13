<?php

use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Limpia reservas DRAFT que nadie pagó y llevan más de 5 minutos sin completarse.
// Esto cubre el caso de usuarios que cierran el navegador desde la pantalla de pago
// sin volver a abrir la Agenda (que es quien limpia DRAFTs propios en mount()).
Schedule::call(function () {
    $eliminadas = Reserva::where('estado', 'DRAFT')
        ->where('created_at', '<', Carbon::now()->subMinutes(5))
        ->delete();

    if ($eliminadas) {
        \Illuminate\Support\Facades\Log::info("Scheduler: {$eliminadas} reservas DRAFT expiradas eliminadas.");
    }
})->everyFiveMinutes()->name('limpiar-drafts-expirados')->withoutOverlapping();
