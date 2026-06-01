<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Configuracion;
use App\Models\Reserva;
use App\Models\Pago;
use App\Models\User;
use Carbon\Carbon;

class Estadisticas extends Component
{
    public int $mes;
    public int $anio;

    public int $totalPeriodo = 0;
    public int $pendientesPago = 0;
    public int $pagadas = 0;
    public int $totalSocios = 0;
    public int $totalNoSocios = 0;
    public int $totalUsuarios = 0;

    public float $montoAutorizado = 0.0;
    public float $montoPendienteRevision = 0.0;
    public float $montoNoSocios = 0.0;
    public float $montoConInvitados = 0.0;
    public int   $cantInvitados = 0;

    public array $estadisticasCanchas = [];

    public function mount(): void
    {
        $this->mes  = (int) Carbon::now()->format('m');
        $this->anio = (int) Carbon::now()->format('Y');
        $this->cargarEstadisticas();
    }

    public function updatedMes(): void   { $this->cargarEstadisticas(); }
    public function updatedAnio(): void  { $this->cargarEstadisticas(); }

    public function cargarEstadisticas(): void
    {
        // Filtrar por el mes del turno (campo `dia` = "lun 02 jun"), no por created_at.
        // Se usa created_at con buffer de ±5 días para acotar la query, luego se filtra por nombre de mes.
        $mesNombres  = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $mesNombre   = $mesNombres[$this->mes];
        $inicioCarga = Carbon::create($this->anio, $this->mes, 1)->subDays(5);
        $finCarga    = Carbon::create($this->anio, $this->mes, 1)->endOfMonth();

        $reservas = Reserva::where('estado', '!=', 'DRAFT')
            ->whereBetween('created_at', [$inicioCarga, $finCarga])
            ->get()
            ->filter(fn($r) => (explode(' ', strtolower(trim($r->dia)))[2] ?? '') === $mesNombre);

        $this->totalPeriodo   = $reservas->count();
        $this->pendientesPago = $reservas->where('esta_pagado', false)->count();
        $this->pagadas        = $reservas->where('esta_pagado', true)->count();

        // Usuarios (siempre totales, no dependen del período)
        $this->totalSocios   = User::where('es_socio', true)->where('rol', '!=', 'admin')->count();
        $this->totalNoSocios = User::where('es_socio', false)->where('rol', '!=', 'admin')->count();
        $this->totalUsuarios = $this->totalSocios + $this->totalNoSocios;

        // Pagos del período
        $reservaIds = $reservas->pluck('id');

        $pagos = Pago::whereIn('reserva_id', $reservaIds)
            ->with('reserva')
            ->get();

        $autorizados = $pagos->where('estado', 'AUTHORIZED');

        $this->montoAutorizado           = (float) $autorizados->sum('monto');
        $this->montoPendienteRevision    = (float) $pagos->where('estado', 'PENDING_REVIEW')->sum('monto');
        $this->montoNoSocios             = (float) $autorizados->filter(fn($p) => empty($p->reserva->invitados))->sum('monto');
        $this->montoConInvitados         = (float) $autorizados->filter(fn($p) => !empty($p->reserva->invitados))->sum('monto');
        $this->cantInvitados             = $reservas->sum(fn($r) => count($r->invitados ?? []));

        // Estadísticas por cancha
        $config      = Configuracion::getConfig();
        $courtCount  = (int) ($config->court_count ?? 4);
        $canchaNames = $config->cancha_names ?? [];

        $this->estadisticasCanchas = [];
        for ($i = 1; $i <= $courtCount; $i++) {
            $reservasCancha = $reservas->where('cancha_id', $i);
            $horasConteo = [];
            foreach ($reservasCancha as $r) {
                $horasConteo[$r->hora] = ($horasConteo[$r->hora] ?? 0) + 1;
            }
            arsort($horasConteo);

            $this->estadisticasCanchas[] = [
                'nombre' => $canchaNames[$i - 1] ?? "Cancha $i",
                'total'  => $reservasCancha->count(),
                'horas'  => array_slice($horasConteo, 0, 5, true),
            ];
        }
    }

    public function render()
    {
        return view('livewire.admin.estadisticas')->layout('layouts.app');
    }
}
