<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reserva;
use App\Models\Bloqueo;
use App\Models\Pago;
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

    // Modal reprogramar
    public bool $modalReprogramar = false;
    public ?int $reprogramarReservaId = null;
    public string $reprogramarDia = '';
    public string $reprogramarHora = '';
    public int $reprogramarCancha = 0;
    public array $dias = [];
    public array $horarios = [];
    public array $canchas = [];
    public array $horasDisponibles = [];

    public function mount(): void
    {
        // Cancelar DRAFTs del usuario actual (navegó fuera de la pantalla de pago)
        Reserva::where('estado', 'DRAFT')
            ->where('creador_id', Auth::id())
            ->delete();

        $this->cargarDias();
        $this->cargarHorarios();
        $this->cargarCanchas();
        $this->cargarReservas();
    }

    private function cargarDias(): void
    {
        $this->dias = [];
        for ($i = 0; $i <= 3; $i++) {
            $fecha = Carbon::today()->addDays($i);
            $clave = strtolower($this->nombreDia($fecha->dayOfWeek)) . ' ' . $fecha->format('d') . ' ' . strtolower($this->nombreMes($fecha->month));
            $this->dias[] = [
                'clave'    => $clave,
                'etiqueta' => $i === 0 ? 'Hoy' : $this->nombreDia($fecha->dayOfWeek) . ' ' . $fecha->format('d'),
                'fecha'    => $fecha->toDateString(),
            ];
        }
    }

    private function nombreDia(int $dow): string
    {
        return ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'][$dow];
    }

    private function nombreMes(int $m): string
    {
        return ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'][$m];
    }

    private function cargarCanchas(): void
    {
        $config = Configuracion::getConfig();
        $count = $config ? $config->court_count : 4;
        $names = $config ? ($config->cancha_names ?? []) : [];

        $this->canchas = [];
        for ($i = 1; $i <= $count; $i++) {
            $this->canchas[] = [
                'id'     => $i,
                'nombre' => $names[$i - 1] ?? (string) $i,
            ];
        }
    }

    private function cargarHorarios(): void
    {
        $config = Configuracion::getConfig();
        $this->horarios = $config && $config->slots ? $config->slots : [
            '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
            '11:00', '11:30', '12:00', '12:30', '13:00', '13:30',
            '14:00', '14:30', '15:00', '15:30', '16:00', '16:30',
            '17:00', '17:30', '18:00', '18:30', '19:00', '19:30',
            '20:00', '20:30', '21:00',
        ];
    }

    public function cargarReservas(): void
    {
        $esControl = Auth::user()->rol === 'control';

        if ($esControl) {
            $this->cargarReservasControl();
            return;
        }

        $userId   = Auth::id();
        $reservas = Reserva::whereJsonContains('jugadores_ids', $userId)
            ->where('estado', '!=', 'DRAFT')
            ->get();

        [$usuariosPorId, $pagosPorReserva] = $this->batchCargarUsuariosYPagos($reservas);

        $this->reservas = $reservas
            ->map(function ($r) use ($userId, $usuariosPorId, $pagosPorReserva) {
                $pagosDeEstaReserva = $pagosPorReserva->get($r->id, collect());
                $pagosKeyBy         = $pagosDeEstaReserva->keyBy('user_id');
                $miPago             = $pagosDeEstaReserva->firstWhere('user_id', $userId);

                $jugadoresConPago = collect($r->jugadores_ids ?? [])
                    ->map(fn($id) => $usuariosPorId->get($id))
                    ->filter()
                    ->map(fn($u) => array_merge($u->toArray(), [
                        'es_invitado' => false,
                        'pago_estado' => $pagosKeyBy->get($u->id)?->estado ?? null,
                    ]))->toArray();

                $invitados = collect($r->invitados ?? [])->map(fn($inv) => [
                    'id'          => null,
                    'nombre'      => 'Invitado',
                    'apellido'    => $inv['apellido'] ?? '',
                    'es_socio'    => false,
                    'es_invitado' => true,
                ])->toArray();

                return array_merge($r->toArray(), [
                    'jugadores'      => array_merge($jugadoresConPago, $invitados),
                    'vencida'        => $this->estaVencida($r->dia, $r->hora),
                    'fecha_sort'     => $this->parsearFechaHora($r->dia, $r->hora),
                    'mi_pago_estado' => $miPago?->estado ?? null,
                    'mi_pago_monto'  => $miPago?->monto ?? 0,
                ]);
            })
            ->filter(fn($r) => !$r['vencida'] || $r['estado'] === 'SUSPENDIDA')
            ->sortBy('fecha_sort')
            ->values()
            ->toArray();
    }

    private function cargarReservasControl(): void
    {
        $hoy = Carbon::today();
        $diaStr = strtolower(
            ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'][$hoy->dayOfWeek]
            . ' ' . $hoy->format('d') . ' '
            . ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'][$hoy->month]
        );

        $reservas = Reserva::where('dia', $diaStr)
            ->where('estado', '!=', 'SUSPENDIDA')
            ->get();

        [$usuariosPorId, $pagosPorReserva] = $this->batchCargarUsuariosYPagos($reservas);

        $this->reservas = $reservas
            ->map(function ($r) use ($usuariosPorId, $pagosPorReserva) {
                $pagosDeEstaReserva = $pagosPorReserva->get($r->id, collect());
                $pagosKeyBy         = $pagosDeEstaReserva->keyBy('user_id');

                $jugadoresConPago = collect($r->jugadores_ids ?? [])
                    ->map(fn($id) => $usuariosPorId->get($id))
                    ->filter()
                    ->map(fn($u) => array_merge($u->toArray(), [
                        'es_invitado' => false,
                        'pago_estado' => $pagosKeyBy->get($u->id)?->estado ?? null,
                    ]))->toArray();

                $invitados = collect($r->invitados ?? [])->map(fn($inv) => [
                    'id'          => null,
                    'nombre'      => 'Invitado',
                    'apellido'    => $inv['apellido'] ?? '',
                    'es_socio'    => false,
                    'es_invitado' => true,
                    'pago_estado' => null,
                ])->toArray();

                return array_merge($r->toArray(), [
                    'jugadores'      => array_merge($jugadoresConPago, $invitados),
                    'vencida'        => false,
                    'fecha_sort'     => $this->parsearFechaHora($r->dia, $r->hora),
                    'mi_pago_estado' => null,
                    'mi_pago_monto'  => 0,
                ]);
            })
            ->sortBy('fecha_sort')
            ->values()
            ->toArray();
    }

    private function batchCargarUsuariosYPagos(\Illuminate\Support\Collection $reservas): array
    {
        $todosUserIds    = $reservas->flatMap(fn($r) => $r->jugadores_ids ?? [])->unique()->values()->toArray();
        $todosReservaIds = $reservas->pluck('id')->toArray();

        $usuariosPorId = !empty($todosUserIds)
            ? User::whereIn('id', $todosUserIds)->get(['id', 'nombre', 'apellido', 'es_socio'])->keyBy('id')
            : collect();

        $pagosPorReserva = !empty($todosReservaIds)
            ? Pago::whereIn('reserva_id', $todosReservaIds)->get()->groupBy('reserva_id')
            : collect();

        return [$usuariosPorId, $pagosPorReserva];
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

    public function confirmarReprogramar(int $reservaId): void
    {
        $reserva = Reserva::find($reservaId);
        if (!$reserva || !in_array(Auth::id(), $reserva->jugadores_ids ?? [])) return;

        $this->reprogramarReservaId = $reservaId;
        $this->reprogramarCancha = $reserva->cancha_id;
        $this->reprogramarDia = $this->dias[0]['clave'];
        $this->reprogramarHora = '';
        $this->actualizarHorasDisponibles();
        $this->modalReprogramar = true;
    }

    public function seleccionarCanchaReprogramar(int $canchaId): void
    {
        $this->reprogramarCancha = $canchaId;
        $this->reprogramarHora = '';
        $this->actualizarHorasDisponibles();
    }

    public function seleccionarDiaReprogramar(string $clave): void
    {
        $this->reprogramarDia = $clave;
        $this->reprogramarHora = '';
        $this->actualizarHorasDisponibles();
    }

    public function actualizarHorasDisponibles(): void
    {
        if (!$this->reprogramarDia || !$this->reprogramarCancha) {
            $this->horasDisponibles = [];
            return;
        }

        $diaInfo = collect($this->dias)->firstWhere('clave', $this->reprogramarDia);
        if (!$diaInfo) {
            $this->horasDisponibles = [];
            return;
        }

        $reservasOcupadas = Reserva::where('dia', $this->reprogramarDia)
            ->where('cancha_id', $this->reprogramarCancha)
            ->where('id', '!=', $this->reprogramarReservaId)
            ->pluck('hora')
            ->toArray();

        $bloqueos = Bloqueo::where('dia', $this->reprogramarDia)->get();
        $horasBloqueadas = [];
        foreach ($bloqueos as $b) {
            if ($b->hora === null) {
                $this->horasDisponibles = [];
                return;
            }
            if ($b->cancha_id === null || $b->cancha_id == $this->reprogramarCancha) {
                $horasBloqueadas[] = $b->hora;
            }
        }

        // Jugadores involucrados en la reserva que se reprograma (incluye al usuario actual)
        $reservaActual = Reserva::find($this->reprogramarReservaId);
        $jugadoresIds = $reservaActual ? ($reservaActual->jugadores_ids ?? []) : [Auth::id()];

        // Horas conflictivas de todos los jugadores de la reserva
        $horasConflicto = [];
        foreach ($jugadoresIds as $jugadorId) {
            $horasJugador = Reserva::where('dia', $this->reprogramarDia)
                ->where('id', '!=', $this->reprogramarReservaId)
                ->whereJsonContains('jugadores_ids', $jugadorId)
                ->pluck('hora')
                ->toArray();

            foreach ($horasJugador as $horaReservada) {
                $horasConflicto[] = $horaReservada;
                $idx = array_search($horaReservada, $this->horarios);
                if ($idx !== false && $idx > 0) {
                    $horasConflicto[] = $this->horarios[$idx - 1];
                }
                if ($idx !== false && $idx < count($this->horarios) - 1) {
                    $horasConflicto[] = $this->horarios[$idx + 1];
                }
            }
        }

        $fecha = $diaInfo['fecha'];
        $this->horasDisponibles = array_values(array_filter($this->horarios, function ($hora) use ($fecha, $reservasOcupadas, $horasBloqueadas, $horasConflicto) {
            if (in_array($hora, $reservasOcupadas)) return false;
            if (in_array($hora, $horasBloqueadas)) return false;
            if (in_array($hora, $horasConflicto)) return false;
            return !Carbon::parse($fecha . ' ' . $hora)->isPast();
        }));
    }

    public function reprogramarReserva(): void
    {
        if (!$this->reprogramarReservaId || !$this->reprogramarDia || !$this->reprogramarHora) {
            $this->dispatch('toast', message: 'Seleccioná un día y horario.', type: 'error');
            return;
        }

        $reserva = Reserva::find($this->reprogramarReservaId);
        if (!$reserva || !in_array(Auth::id(), $reserva->jugadores_ids ?? [])) {
            $this->dispatch('toast', message: 'Reserva no encontrada.', type: 'error');
            return;
        }

        $existente = Reserva::where('dia', $this->reprogramarDia)
            ->where('hora', $this->reprogramarHora)
            ->where('cancha_id', $this->reprogramarCancha)
            ->where('id', '!=', $this->reprogramarReservaId)
            ->first();

        if ($existente) {
            $this->dispatch('toast', message: 'Ese turno ya fue tomado. Elegí otro.', type: 'error');
            $this->actualizarHorasDisponibles();
            return;
        }

        // Verificar que ningún jugador de la reserva tenga conflicto en el nuevo horario
        $idxSeleccionado = array_search($this->reprogramarHora, $this->horarios);
        foreach ($reserva->jugadores_ids ?? [] as $jugadorId) {
            $horasJugador = Reserva::where('dia', $this->reprogramarDia)
                ->where('id', '!=', $this->reprogramarReservaId)
                ->whereJsonContains('jugadores_ids', $jugadorId)
                ->pluck('hora')
                ->toArray();

            foreach ($horasJugador as $horaOcupada) {
                $idxOcupado = array_search($horaOcupada, $this->horarios);
                if ($idxOcupado !== false && $idxSeleccionado !== false && abs($idxOcupado - $idxSeleccionado) <= 1) {
                    $this->dispatch('toast', message: 'Uno de los jugadores ya tiene un turno en ese horario o consecutivo.', type: 'error');
                    $this->actualizarHorasDisponibles();
                    return;
                }
            }
        }

        $nuevoEstado = $reserva->esta_pagado ? 'AUTHORIZED' : 'PENDING';
        $reserva->update([
            'dia'               => $this->reprogramarDia,
            'hora'              => $this->reprogramarHora,
            'cancha_id'         => $this->reprogramarCancha,
            'estado'            => $nuevoEstado,
            'suspension_motivo' => null,
        ]);

        $this->modalReprogramar = false;
        $this->cargarReservas();
        $this->dispatch('toast', message: 'Turno reprogramado correctamente.', type: 'success');
    }

    public function render()
    {
        return view('livewire.mis-turnos')->layout('layouts.app');
    }
}
