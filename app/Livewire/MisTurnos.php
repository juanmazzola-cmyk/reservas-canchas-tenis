<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MisTurnos extends Component
{
    public array $reservas = [];

    // Modal cancelar
    public bool $modalCancelar = false;
    public ?int $cancelarReservaId = null;

    public function mount(): void
    {
        $this->cargarReservas();
    }

    public function cargarReservas(): void
    {
        $userId = Auth::id();
        $this->reservas = Reserva::whereJsonContains('jugadores_ids', $userId)
            ->get()
            ->map(function ($r) {
                $jugadores = User::whereIn('id', $r->jugadores_ids ?? [])->get(['id', 'nombre', 'apellido', 'es_socio']);
                return array_merge($r->toArray(), [
                    'jugadores'  => $jugadores->toArray(),
                    'vencida'    => $this->estaVencida($r->dia, $r->hora),
                    'fecha_sort' => $this->parsearFechaHora($r->dia, $r->hora),
                ]);
            })
            ->filter(fn($r) => !$r['vencida'])
            ->sortBy('fecha_sort')
            ->values()
            ->toArray();
    }

    private function parsearFechaHora(string $dia, string $hora): string
    {
        try {
            $partes = explode(' ', $dia);
            if (count($partes) >= 3) {
                $diaNum = $partes[1];
                $mesStr = $partes[2];
                $meses = [
                    'ene' => 1, 'feb' => 2, 'mar' => 3, 'abr' => 4,
                    'may' => 5, 'jun' => 6, 'jul' => 7, 'ago' => 8,
                    'sep' => 9, 'oct' => 10, 'nov' => 11, 'dic' => 12,
                ];
                $mes = $meses[strtolower($mesStr)] ?? null;
                if ($mes) {
                    $anio = Carbon::now()->year;
                    $fecha = Carbon::create($anio, $mes, (int)$diaNum);
                    return $fecha->toDateString() . ' ' . $hora;
                }
            }
        } catch (\Exception $e) {}
        return '9999-12-31 99:99';
    }

    private function estaVencida(string $dia, string $hora): bool
    {
        // Intentar parsear el día (formato "lun 10 mar")
        try {
            $partes = explode(' ', $dia);
            if (count($partes) >= 3) {
                $diaNum = $partes[1];
                $mesStr = $partes[2];
                $meses = [
                    'ene' => 1, 'feb' => 2, 'mar' => 3, 'abr' => 4,
                    'may' => 5, 'jun' => 6, 'jul' => 7, 'ago' => 8,
                    'sep' => 9, 'oct' => 10, 'nov' => 11, 'dic' => 12,
                ];
                $mes = $meses[strtolower($mesStr)] ?? null;
                if ($mes) {
                    $anio = Carbon::now()->year;
                    $fecha = Carbon::create($anio, $mes, (int)$diaNum);
                    $fechaHora = Carbon::parse($fecha->toDateString() . ' ' . $hora);
                    return $fechaHora->isPast();
                }
            }
        } catch (\Exception $e) {}
        return false;
    }

    public function confirmarCancelar(int $reservaId): void
    {
        $this->cancelarReservaId = $reservaId;
        $this->modalCancelar = true;
    }

    public function cancelarReserva(): void
    {
        $reserva = Reserva::find($this->cancelarReservaId);
        if ($reserva && in_array(Auth::id(), $reserva->jugadores_ids ?? [])) {
            $reserva->delete();
        }

        $this->modalCancelar = false;
        $this->cargarReservas();
        $this->dispatch('toast', message: 'Reserva cancelada.', type: 'success');
    }

    public function render()
    {
        return view('livewire.mis-turnos')->layout('layouts.app');
    }
}
