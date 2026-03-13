<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reserva;
use App\Models\Bloqueo;
use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Agenda extends Component
{
    public string $diaSeleccionado = '';
    public array $dias = [];
    public array $canchas = [];
    public array $horarios = [];
    public array $reservas = [];
    public array $bloqueos = [];
    public array $horasConflicto = []; // hora => 'mismo_horario' | 'consecutivo'

    // Modal reserva
    public bool $modalReserva = false;
    public string $modalHora = '';
    public int $modalCancha = 0;
    public string $modalDia = '';
    public string $modalTipo = 'single'; // 'single' | 'dobles'
    public string $avisoConflicto = '';
    public string $busquedaJugador = '';
    public array $resultadosBusqueda = [];
    public array $jugadoresSeleccionados = [];

// Modal detalle reserva
    public bool $modalDetalle = false;
    public ?int $detalleReservaId = null;

    protected ?Configuracion $config = null;

    public function mount(): void
    {
        $this->config = Configuracion::getConfig();
        $this->cargarDias();
        $this->cargarCanchas();
        $this->cargarHorarios();
        if (empty($this->diaSeleccionado) && !empty($this->dias)) {
            $hoy = collect($this->dias)->firstWhere('fecha', Carbon::today()->toDateString());
            $this->diaSeleccionado = $hoy ? $hoy['clave'] : $this->dias[0]['clave'];
        }
        $this->cargarReservasYBloqueos();
    }

    private function cargarDias(): void
    {
        $inicio = 0;
        $fin = 3;

        $this->dias = [];
        for ($i = $inicio; $i <= $fin; $i++) {
            $fecha = Carbon::today()->addDays($i);
            $diaSemana = $this->nombreDia($fecha->dayOfWeek);
            $diaNum = $fecha->format('d');
            $mes = $this->nombreMes($fecha->month);
            $clave = strtolower($diaSemana) . ' ' . $diaNum . ' ' . strtolower($mes);
            $this->dias[] = [
                'clave'     => $clave,
                'etiqueta'  => $i === 0 ? 'Hoy' : ($i === -1 ? 'Ayer' : $diaSemana . ' ' . $diaNum),
                'fecha'     => $fecha->toDateString(),
                'timestamp' => $fecha->timestamp,
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
        $count = $this->config ? $this->config->court_count : 4;
        $names = $this->config ? ($this->config->cancha_names ?? []) : [];

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
        $this->horarios = $this->config && $this->config->slots ? $this->config->slots : [
            '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
            '11:00', '11:30', '12:00', '12:30', '13:00', '13:30',
            '14:00', '14:30', '15:00', '15:30', '16:00', '16:30',
            '17:00', '17:30', '18:00', '18:30', '19:00', '19:30',
            '20:00', '20:30', '21:00',
        ];
    }

    public function cargarReservasYBloqueos(): void
    {
        $this->reservas = Reserva::where('dia', $this->diaSeleccionado)
            ->get()
            ->toArray();

        $this->bloqueos = Bloqueo::where('dia', $this->diaSeleccionado)
            ->get()
            ->toArray();

        $this->calcularHorasConflicto();
    }

    private function calcularHorasConflicto(): void
    {
        $this->horasConflicto = [];

        if (Auth::user()->rol !== 'usuario') return;

        $userId = Auth::id();

        foreach ($this->reservas as $r) {
            if (!in_array($userId, $r['jugadores_ids'] ?? [])) continue;

            // La misma hora en otra cancha sería conflicto
            $this->horasConflicto[$r['hora']] = 'mismo_horario';

            // Marcar consecutivas
            $idx = array_search($r['hora'], $this->horarios);
            if ($idx !== false && $idx > 0) {
                $h = $this->horarios[$idx - 1];
                if (!isset($this->horasConflicto[$h])) {
                    $this->horasConflicto[$h] = 'consecutivo';
                }
            }
            if ($idx !== false && $idx < count($this->horarios) - 1) {
                $h = $this->horarios[$idx + 1];
                if (!isset($this->horasConflicto[$h])) {
                    $this->horasConflicto[$h] = 'consecutivo';
                }
            }
        }
    }

    public function seleccionarDia(string $clave): void
    {
        $this->diaSeleccionado = $clave;
        $this->cargarReservasYBloqueos();
    }

    public function getCeldaInfo(string $hora, int $cancha): array
    {
        $rol = Auth::user()->rol;
        $userId = Auth::id();

        // Verificar bloqueo de día completo
        foreach ($this->bloqueos as $b) {
            if ($b['hora'] === null && ($b['cancha_id'] === null || $b['cancha_id'] == $cancha)) {
                return ['tipo' => 'bloqueada', 'razon' => $b['razon'] ?? ''];
            }
        }

        // Verificar bloqueo de hora
        foreach ($this->bloqueos as $b) {
            if ($b['hora'] === $hora && ($b['cancha_id'] === null || $b['cancha_id'] == $cancha)) {
                return ['tipo' => 'bloqueada', 'razon' => $b['razon'] ?? ''];
            }
        }

        // Vencida y sin_anticipacion solo aplican a usuarios
        if ($rol === 'usuario') {
            if ($this->estaCeldaVencida($hora)) {
                return ['tipo' => 'vencida'];
            }
        }

        // Buscar reserva
        foreach ($this->reservas as $r) {
            if ($r['hora'] === $hora && $r['cancha_id'] == $cancha) {
                $esMia = in_array($userId, $r['jugadores_ids'] ?? []);
                $apellidos = [];
                if ($rol === 'admin' || $rol === 'control' || $esMia) {
                    $jugadores = User::whereIn('id', $r['jugadores_ids'] ?? [])->get();
                    $apellidos = $jugadores->pluck('apellido')->toArray();
                }
                return [
                    'tipo'          => 'ocupada',
                    'reserva_id'    => $r['id'],
                    'apellidos'     => $apellidos,
                    'esta_pagado'   => $r['esta_pagado'],
                    'estado'        => $r['estado'],
                    'comprobante'   => $r['comprobante'] ?? null,
                    'es_mia'        => $esMia,
                ];
            }
        }

        if ($rol === 'usuario' && !$this->puedeReservar($hora)) {
            return ['tipo' => 'sin_anticipacion'];
        }

        return ['tipo' => 'libre'];
    }

    private function estaCeldaVencida(string $hora): bool
    {
        $diaInfo = collect($this->dias)->firstWhere('clave', $this->diaSeleccionado);
        if (!$diaInfo) return false;

        $fechaHora = Carbon::parse($diaInfo['fecha'] . ' ' . $hora)->addMinutes(10);
        return $fechaHora->isPast();
    }

    private function puedeReservar(string $hora = ''): bool
    {
        $diaInfo = collect($this->dias)->firstWhere('clave', $this->diaSeleccionado);
        if (!$diaInfo) return false;

        $horaTarget = $hora ?: $this->modalHora;
        $config = Configuracion::getConfig();
        $limitHoras = $config ? (int) $config->advance_booking_limit_hours : 96;
        $fechaHora = Carbon::parse($diaInfo['fecha'] . ' ' . $horaTarget);
        $horasAnticipacion = now()->diffInHours($fechaHora, false);

        // Si ya pasó la hora, estaCeldaVencida se encarga → no generar estado intermedio
        if ($horasAnticipacion < 0) return true;

        return $horasAnticipacion <= $limitHoras;
    }

    public function seleccionarTurno(string $hora, int $cancha): void
    {
        $celda = $this->getCeldaInfo($hora, $cancha);

        if ($celda['tipo'] === 'ocupada') {
            if (Auth::user()->rol === 'control') return;
            $this->detalleReservaId = $celda['reserva_id'];
            $this->modalDetalle = true;
            return;
        }

        if ($celda['tipo'] === 'sin_anticipacion') return;

        if ($celda['tipo'] !== 'libre') return;

        if (Auth::user()->rol !== 'usuario') return;

        $this->modalHora = $hora;
        $this->modalCancha = $cancha;
        $this->modalDia = $this->diaSeleccionado;
        $this->modalTipo = 'single';
        $this->jugadoresSeleccionados = [];
        $this->busquedaJugador = '';
        $this->resultadosBusqueda = [];

        // Agregar al usuario actual
        $user = Auth::user();
        $this->jugadoresSeleccionados[] = [
            'id'       => $user->id,
            'nombre'   => $user->nombre,
            'apellido' => $user->apellido,
            'es_socio' => $user->es_socio,
        ];

        $this->verificarConflictos();
        $this->modalReserva = true;
    }

    public function buscarJugador(): void
    {
        if (strlen($this->busquedaJugador) < 2) {
            $this->resultadosBusqueda = [];
            return;
        }

        $idsSeleccionados = collect($this->jugadoresSeleccionados)->pluck('id')->toArray();

        $this->resultadosBusqueda = User::where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->busquedaJugador . '%')
                  ->orWhere('apellido', 'like', '%' . $this->busquedaJugador . '%');
            })
            ->whereNotIn('id', $idsSeleccionados)
            ->limit(5)
            ->get(['id', 'nombre', 'apellido', 'es_socio'])
            ->toArray();
    }

    private function verificarConflictos(): void
    {
        $this->avisoConflicto = '';

        if (empty($this->jugadoresSeleccionados) || empty($this->modalHora) || empty($this->modalDia)) {
            return;
        }

        $ids = array_column($this->jugadoresSeleccionados, 'id');

        // Mismo horario en cualquier otra cancha
        $mismaHora = Reserva::where('dia', $this->modalDia)
            ->where('hora', $this->modalHora)
            ->where('cancha_id', '!=', $this->modalCancha)
            ->get();

        foreach ($mismaHora as $r) {
            if (!empty(array_intersect($ids, $r->jugadores_ids ?? []))) {
                $this->avisoConflicto = 'Vos o tu rival ya tienen reservado en el mismo horario.';
                return;
            }
        }

        // Horarios consecutivos en cualquier cancha
        $horaIdx = array_search($this->modalHora, $this->horarios);
        $horasConsecutivas = [];
        if ($horaIdx !== false && $horaIdx > 0) {
            $horasConsecutivas[] = $this->horarios[$horaIdx - 1];
        }
        if ($horaIdx !== false && $horaIdx < count($this->horarios) - 1) {
            $horasConsecutivas[] = $this->horarios[$horaIdx + 1];
        }

        if (!empty($horasConsecutivas)) {
            $reservasConsecutivas = Reserva::where('dia', $this->modalDia)
                ->whereIn('hora', $horasConsecutivas)
                ->get();

            foreach ($reservasConsecutivas as $r) {
                if (!empty(array_intersect($ids, $r->jugadores_ids ?? []))) {
                    $this->avisoConflicto = 'Vos o tu rival tiene turno reservado, no podés reservar dos turnos consecutivos.';
                    return;
                }
            }
        }
    }

    public function setTipo(string $tipo): void
    {
        $this->modalTipo = $tipo;
        // Si cambia a single y hay más de 2, quitar los extras (excepto el propio usuario)
        $max = $tipo === 'single' ? 2 : 4;
        if (count($this->jugadoresSeleccionados) > $max) {
            $this->jugadoresSeleccionados = array_values(
                array_slice($this->jugadoresSeleccionados, 0, $max)
            );
        }
        $this->busquedaJugador = '';
        $this->resultadosBusqueda = [];
    }

    public function agregarJugador(int $userId): void
    {
        $max = $this->modalTipo === 'single' ? 2 : 4;
        if (count($this->jugadoresSeleccionados) >= $max) {
            $this->dispatch('toast', message: $this->modalTipo === 'single' ? 'Single: máximo 1 rival.' : 'Dobles: máximo 3 rivales.', type: 'warning');
            return;
        }

        $user = User::find($userId);
        if (!$user) return;

        foreach ($this->jugadoresSeleccionados as $j) {
            if ($j['id'] == $userId) return;
        }

        $this->jugadoresSeleccionados[] = [
            'id'       => $user->id,
            'nombre'   => $user->nombre,
            'apellido' => $user->apellido,
            'es_socio' => $user->es_socio,
        ];

        $this->busquedaJugador = '';
        $this->resultadosBusqueda = [];
        $this->verificarConflictos();
    }

    public function quitarJugador(int $userId): void
    {
        if ($userId === Auth::id()) {
            $this->dispatch('toast', message: 'No podés quitarte a vos mismo.', type: 'warning');
            return;
        }
        $this->jugadoresSeleccionados = array_values(
            array_filter($this->jugadoresSeleccionados, fn($j) => $j['id'] !== $userId)
        );
        $this->verificarConflictos();
    }

    public function confirmarReserva(): void
    {
        if (empty($this->jugadoresSeleccionados)) {
            $this->dispatch('toast', message: 'Agregá al menos un jugador.', type: 'error');
            return;
        }

        $ids = array_column($this->jugadoresSeleccionados, 'id');

        // Verificar que la celda sigue libre
        $existente = Reserva::where('dia', $this->modalDia)
            ->where('hora', $this->modalHora)
            ->where('cancha_id', $this->modalCancha)
            ->first();

        if ($existente) {
            $this->dispatch('toast', message: 'Ese turno ya fue tomado.', type: 'error');
            $this->modalReserva = false;
            $this->cargarReservasYBloqueos();
            return;
        }

        // Verificar conflictos: mismo horario o horario consecutivo para cualquier jugador
        $horaIdx = array_search($this->modalHora, $this->horarios);
        $horasAVerificar = [$this->modalHora];
        if ($horaIdx !== false && $horaIdx > 0) {
            $horasAVerificar[] = $this->horarios[$horaIdx - 1];
        }
        if ($horaIdx !== false && $horaIdx < count($this->horarios) - 1) {
            $horasAVerificar[] = $this->horarios[$horaIdx + 1];
        }

        $reservasExistentes = Reserva::where('dia', $this->modalDia)
            ->whereIn('hora', $horasAVerificar)
            ->get();

        foreach ($reservasExistentes as $r) {
            $idsEnConflicto = array_intersect($ids, $r->jugadores_ids ?? []);
            if (!empty($idsEnConflicto)) {
                $jugador = collect($this->jugadoresSeleccionados)
                    ->first(fn($j) => in_array($j['id'], $idsEnConflicto));
                $nombre = $jugador ? $jugador['apellido'] . ', ' . $jugador['nombre'] : 'Un jugador';
                if ($r->hora === $this->modalHora) {
                    $this->dispatch('toast', message: "{$nombre} ya tiene un turno a las {$r->hora}.", type: 'error');
                } else {
                    $this->dispatch('toast', message: "{$nombre} tiene un turno consecutivo a las {$r->hora}.", type: 'error');
                }
                return;
            }
        }

        $todosSocios = collect($this->jugadoresSeleccionados)->every(fn($j) => $j['es_socio']);

        $reserva = Reserva::create([
            'dia'           => $this->modalDia,
            'hora'          => $this->modalHora,
            'cancha_id'     => $this->modalCancha,
            'jugadores_ids' => $ids,
            'creador_id'    => Auth::id(),
            'esta_pagado'   => $todosSocios,
            'estado'        => $todosSocios ? 'AUTHORIZED' : 'PENDING',
        ]);

        $this->modalReserva = false;

        if ($todosSocios) {
            $this->cargarReservasYBloqueos();
            $this->dispatch('toast', message: '¡Reserva confirmada! Todos los jugadores son socios.', type: 'success');
            return;
        }

        $this->redirect(route('pago', $reserva->id));
    }

    public function bloquearCelda(string $hora, int $cancha): void
    {
        if (Auth::user()->rol !== 'admin') return;

        Bloqueo::firstOrCreate([
            'dia'       => $this->diaSeleccionado,
            'hora'      => $hora,
            'cancha_id' => $cancha,
        ], ['razon' => null]);

        $this->cargarReservasYBloqueos();
        $this->dispatch('toast', message: "Bloqueado {$hora} — Cancha {$cancha}.", type: 'success');
    }

    public function bloquearCancha(int $cancha): void
    {
        if (Auth::user()->rol !== 'admin') return;

        // Quitar bloqueos previos de esa cancha en ese día
        Bloqueo::where('dia', $this->diaSeleccionado)
            ->where('cancha_id', $cancha)
            ->whereNull('hora')
            ->delete();

        Bloqueo::create([
            'dia'       => $this->diaSeleccionado,
            'hora'      => null,
            'cancha_id' => $cancha,
            'razon'     => 'Cancha bloqueada',
        ]);

        $this->cargarReservasYBloqueos();
        $this->dispatch('toast', message: "Cancha {$cancha} bloqueada.", type: 'success');
    }

    public function bloquearDia(): void
    {
        if (Auth::user()->rol !== 'admin') return;

        Bloqueo::where('dia', $this->diaSeleccionado)->delete();

        Bloqueo::create([
            'dia'       => $this->diaSeleccionado,
            'hora'      => null,
            'cancha_id' => null,
            'razon'     => 'Día bloqueado completo',
        ]);

        $this->cargarReservasYBloqueos();
        $this->dispatch('toast', message: 'Día completo bloqueado.', type: 'success');
    }

    public function desbloquearDia(): void
    {
        if (Auth::user()->rol !== 'admin') return;

        Bloqueo::where('dia', $this->diaSeleccionado)
            ->whereNull('hora')
            ->whereNull('cancha_id')
            ->delete();

        $this->cargarReservasYBloqueos();
        $this->dispatch('toast', message: 'Día desbloqueado.', type: 'success');
    }

    public function bloquearHora(string $hora): void
    {
        if (Auth::user()->rol !== 'admin') return;

        Bloqueo::where('dia', $this->diaSeleccionado)
            ->where('hora', $hora)
            ->whereNull('cancha_id')
            ->delete();

        Bloqueo::create([
            'dia'       => $this->diaSeleccionado,
            'hora'      => $hora,
            'cancha_id' => null,
            'razon'     => 'Horario bloqueado',
        ]);

        $this->cargarReservasYBloqueos();
        $this->dispatch('toast', message: "Horario {$hora} bloqueado.", type: 'success');
    }

    public function desbloquearHora(string $hora): void
    {
        if (Auth::user()->rol !== 'admin') return;

        Bloqueo::where('dia', $this->diaSeleccionado)
            ->where('hora', $hora)
            ->whereNull('cancha_id')
            ->delete();

        $this->cargarReservasYBloqueos();
        $this->dispatch('toast', message: "Horario {$hora} desbloqueado.", type: 'success');
    }

    public function desbloquearCancha(int $cancha): void
    {
        if (Auth::user()->rol !== 'admin') return;

        Bloqueo::where('dia', $this->diaSeleccionado)
            ->where('cancha_id', $cancha)
            ->whereNull('hora')
            ->delete();

        $this->cargarReservasYBloqueos();
        $this->dispatch('toast', message: "Cancha {$cancha} desbloqueada.", type: 'success');
    }

    public function desbloquear(string $hora, int $cancha): void
    {
        if (Auth::user()->rol !== 'admin') return;

        Bloqueo::where('dia', $this->diaSeleccionado)
            ->where('hora', $hora)
            ->where(function ($q) use ($cancha) {
                $q->where('cancha_id', $cancha)->orWhereNull('cancha_id');
            })
            ->delete();

        $this->cargarReservasYBloqueos();
        $this->dispatch('toast', message: 'Bloqueo eliminado.', type: 'success');
    }

    public function cancelarReservaAdmin(int $reservaId): void
    {
        if (Auth::user()->rol !== 'admin') return;
        Reserva::destroy($reservaId);
        $this->modalDetalle = false;
        $this->cargarReservasYBloqueos();
        $this->dispatch('toast', message: 'Reserva cancelada.', type: 'success');
    }

    public function marcarPagado(int $reservaId): void
    {
        if (Auth::user()->rol !== 'admin') return;
        $reserva = Reserva::find($reservaId);
        if ($reserva) {
            $reserva->update(['esta_pagado' => true, 'estado' => 'AUTHORIZED']);
        }
        $this->modalDetalle = false;
        $this->cargarReservasYBloqueos();
        $this->dispatch('toast', message: 'Pago autorizado.', type: 'success');
        $this->dispatch('reservaAutorizada');
    }

    public function autorizarPago(int $reservaId): void
    {
        if (Auth::user()->rol !== 'admin') return;
        $reserva = Reserva::find($reservaId);
        if ($reserva) {
            $reserva->update(['esta_pagado' => true, 'estado' => 'AUTHORIZED']);
        }
        $this->modalDetalle = false;
        $this->cargarReservasYBloqueos();
        $this->dispatch('toast', message: '¡Reserva autorizada!', type: 'success');
        $this->dispatch('reservaAutorizada');
    }

    public function getDetalleReserva(): ?Reserva
    {
        if (!$this->detalleReservaId) return null;
        return Reserva::find($this->detalleReservaId);
    }

    public function render()
    {
        return view('livewire.agenda', [
            'detalleReserva' => $this->getDetalleReserva(),
        ])->layout('layouts.app');
    }
}
