<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Pago;
use App\Models\Reserva;
use Carbon\Carbon;

#[Layout('layouts.app')]
class Comprobantes extends Component
{
    public ?int $modalPagoId = null;

    public function abrirModal(int $pagoId): void
    {
        $this->modalPagoId = $pagoId;
    }

    public function cerrarModal(): void
    {
        $this->modalPagoId = null;
    }

    public function render()
    {
        // Reservas con al menos un comprobante, que no hayan vencido (dia de hoy en adelante)
        // Tomamos los pagos con comprobante y filtramos por reservas vigentes
        $pagos = Pago::whereNotNull('comprobante')
            ->with(['user', 'reserva'])
            ->orderByDesc('updated_at')
            ->get();

        // Agrupar por reserva y filtrar reservas que no pasaron aún
        $reservas = $pagos
            ->groupBy('reserva_id')
            ->map(fn($pagosDeLaReserva) => [
                'reserva' => $pagosDeLaReserva->first()->reserva,
                'pagos'   => $pagosDeLaReserva,
            ])
            ->filter(fn($item) => $item['reserva'] !== null && !$this->reservaVencida($item['reserva']))
            ->sortBy(fn($item) => $this->parsearFechaHora($item['reserva']->dia, $item['reserva']->hora))
            ->values();

        $modalPago = $this->modalPagoId
            ? Pago::with('user')->find($this->modalPagoId)
            : null;

        return view('livewire.admin.comprobantes', compact('reservas', 'modalPago'));
    }

    private function reservaVencida(Reserva $r): bool
    {
        $ts = $this->parsearFechaHora($r->dia, $r->hora);
        return $ts && $ts->addMinutes(90)->isPast();
    }

    private function parsearFechaHora(string $dia, string $hora): ?Carbon
    {
        try {
            $diasEs  = ['dom' => 0, 'lun' => 1, 'mar' => 2, 'mié' => 3, 'jue' => 4, 'vie' => 5, 'sáb' => 6];
            $mesesEs = ['ene' => 1, 'feb' => 2, 'mar' => 3, 'abr' => 4, 'may' => 5, 'jun' => 6,
                        'jul' => 7, 'ago' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dic' => 12];

            $partes = explode(' ', strtolower(trim($dia)));
            $diaNum = (int) $partes[1];
            $mes    = $mesesEs[$partes[2]] ?? null;
            if (!$mes) return null;

            $anio = Carbon::now()->year;
            $dt   = Carbon::create($anio, $mes, $diaNum, ...explode(':', $hora));
            if ($dt->isPast() && $dt->diffInMonths(now()) > 6) {
                $dt->addYear();
            }
            return $dt;
        } catch (\Throwable) {
            return null;
        }
    }
}
