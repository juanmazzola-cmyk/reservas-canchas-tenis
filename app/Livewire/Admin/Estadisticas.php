<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Reserva;
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
    public array $jugadoresTop = [];

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
        $inicio = Carbon::create($this->anio, $this->mes, 1)->startOfMonth();
        $fin    = Carbon::create($this->anio, $this->mes, 1)->endOfMonth();

        $reservas = Reserva::whereBetween('created_at', [$inicio, $fin])->get();

        $this->totalPeriodo   = $reservas->count();
        $this->pendientesPago = $reservas->where('esta_pagado', false)->count();
        $this->pagadas        = $reservas->where('esta_pagado', true)->count();

        // Usuarios (siempre totales, no dependen del período)
        $this->totalSocios   = User::where('es_socio', true)->where('rol', '!=', 'admin')->count();
        $this->totalNoSocios = User::where('es_socio', false)->where('rol', '!=', 'admin')->count();
        $this->totalUsuarios = $this->totalSocios + $this->totalNoSocios;

        // Ranking de jugadores: contar apariciones en jugadores_ids
        $conteo = [];
        foreach ($reservas as $r) {
            foreach ($r->jugadores_ids ?? [] as $uid) {
                $conteo[$uid] = ($conteo[$uid] ?? 0) + 1;
            }
        }
        arsort($conteo);

        $usuarios = User::whereIn('id', array_keys($conteo))->get()->keyBy('id');

        $this->jugadoresTop = [];
        foreach ($conteo as $uid => $cant) {
            if (!isset($usuarios[$uid])) continue;
            $u = $usuarios[$uid];
            $this->jugadoresTop[] = [
                'nombre'   => $u->nombre . ' ' . $u->apellido,
                'es_socio' => $u->es_socio,
                'reservas' => $cant,
            ];
        }
    }

    public function render()
    {
        return view('livewire.admin.estadisticas')->layout('layouts.app');
    }
}
