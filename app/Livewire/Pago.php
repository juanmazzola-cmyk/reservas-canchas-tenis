<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Reserva;
use App\Models\Pago as PagoModel;
use App\Models\User;
use App\Models\Configuracion;
use App\Services\ComprobanteVerificador;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Pago extends Component
{
    use WithFileUploads;

    public int   $reservaId    = 0;
    public int   $miPagoId     = 0;
    public float $totalAPagar    = 0;  // monto individual del usuario (su parte)
    public float $totalReserva  = 0;  // total de toda la reserva (todos los no-socios)
    public float $montoYaPagado = 0;  // suma de pagos ya AUTHORIZED
    public float $montoRestante = 0;  // totalReserva - montoYaPagado

    public array  $jugadores    = [];
    public int    $cantNoSocios = 0;

    // Estado del pago del usuario actual
    public string $miPagoEstado     = '';    // PENDIENTE | AUTHORIZED | PENDING_REVIEW
    public bool   $noNecesitosPagar = false; // el usuario es socio, no tiene pago asignado
    public bool   $hayInvitados     = false;

    // Socio que elige pagar voluntariamente
    public bool   $puedeOfrecerPago = false; // socio con rival no-socio pendiente
    public bool   $socioQuierePagar = false; // eligió pagar él

    // Estado general de la reserva
    public bool   $todosAutorizados = false;

    // Subida de comprobante
    public $comprobante;
    public bool   $enviado      = false;
    public ?array $verificacion = null;
    public string $errorImporte = '';
    public bool   $pagoDemas    = false;

    // Datos del turno
    public string $turno_dia    = '';
    public string $turno_hora   = '';
    public int    $turno_cancha = 0;
    public string $turno_estado = '';

    // WhatsApp admin
    public string $waUrl = '';

    // Resultado MercadoPago (flujo legacy)
    public string $mp_result = '';

    public function mount(int $reserva): void
    {
        $r = Reserva::find($reserva);

        if (!$r) {
            $this->redirect(route('agenda'));
            return;
        }

        $userId  = Auth::id();
        $esAdmin = Auth::user()->rol === 'admin';

        // Solo pueden acceder: jugadores de la reserva y admins
        if (!in_array($userId, $r->jugadores_ids ?? []) && !$esAdmin) {
            $this->redirect(route('agenda'));
            return;
        }

        $this->reservaId    = $r->id;
        $this->turno_dia    = $r->dia;
        $this->turno_hora   = $r->hora;
        $this->turno_cancha = $r->cancha_id;
        $this->turno_estado = $r->estado;
        $this->hayInvitados = !empty($r->invitados);
        $this->mp_result    = session('mp_result', '');

        $config = Configuracion::getConfig();

        // Pagos de la reserva indexados por user_id
        $pagosReserva = PagoModel::where('reserva_id', $r->id)->get()->keyBy('user_id');

        // Armar lista de jugadores para mostrar
        $this->jugadores = User::whereIn('id', $r->jugadores_ids ?? [])
            ->get()
            ->map(fn($u) => [
                'nombre'      => $u->nombre . ' ' . $u->apellido,
                'es_socio'    => $u->es_socio,
                'es_invitado' => false,
                'ya_pago'     => isset($pagosReserva[$u->id]) && in_array($pagosReserva[$u->id]->estado, ['AUTHORIZED', 'PENDING_REVIEW']),
            ])
            ->toArray();

        foreach ($r->invitados ?? [] as $inv) {
            $this->jugadores[] = [
                'nombre'      => $inv['apellido'],
                'es_socio'    => false,
                'es_invitado' => true,
                'ya_pago'     => false,
            ];
        }

        $this->cantNoSocios = collect($this->jugadores)->where('es_socio', false)->count();

        // Buscar el pago del usuario actual
        $miPago = PagoModel::where('reserva_id', $r->id)->where('user_id', $userId)->first();

        if ($miPago) {
            $this->miPagoId     = $miPago->id;
            $this->totalAPagar  = $miPago->monto;
            $this->miPagoEstado = $miPago->estado;

            if (in_array($miPago->estado, ['AUTHORIZED', 'PENDING_REVIEW'])) {
                $this->enviado      = true;
                $this->verificacion = $miPago->verificacion_ia;
            }
        } else {
            // No tiene pago asignado → es socio o admin visualizando
            $this->noNecesitosPagar = true;
        }

        // Total completo de la reserva (suma de todos los pagos)
        $this->totalReserva  = (float) PagoModel::where('reserva_id', $r->id)->sum('monto');
        $this->montoYaPagado = (float) PagoModel::where('reserva_id', $r->id)->where('estado', 'AUTHORIZED')->sum('monto');
        $this->montoRestante = max(0, $this->totalReserva - $this->montoYaPagado);

        // Verificar si TODOS los pagos de la reserva están autorizados
        $this->todosAutorizados = $r->estado === 'AUTHORIZED';

        // Socio puede optar por pagar si hay montos pendientes
        if ($this->noNecesitosPagar && $this->montoRestante > 0) {
            $this->puedeOfrecerPago = true;
            $this->totalAPagar      = $this->montoRestante;
        }

        // Pre-construir URL de WhatsApp para el admin
        if ($config->admin_whatsapp) {
            $tel    = preg_replace('/\D/', '', $config->admin_whatsapp);
            $lineas = collect($this->jugadores)
                ->map(fn($j) => '• ' . $j['nombre'] . ' (' . ($j['es_socio'] ? 'Socio' : ($j['es_invitado'] ? 'Invitado' : 'No socio')) . ')')
                ->implode("\n");
            $msg = urlencode(
                "🎾 *Nueva reserva pendiente de autorización*\n\n" .
                "📅 {$r->dia} a las {$r->hora} — Cancha {$r->cancha_id}\n\n" .
                "👥 Jugadores:\n{$lineas}\n\n" .
                "El comprobante ya fue adjuntado. Por favor revisalo y autorizá la reserva."
            );
            $this->waUrl = "https://wa.me/{$tel}?text={$msg}";
        }
    }

    public function ofrecerPagar(): void
    {
        $primerPago = PagoModel::where('reserva_id', $this->reservaId)
            ->where('estado', 'PENDIENTE')
            ->first();

        if (!$primerPago) return;

        $this->miPagoId         = $primerPago->id;
        $this->miPagoEstado     = 'PENDIENTE';
        $this->socioQuierePagar = true;
    }

    public function updatedComprobante(): void
    {
        $this->errorImporte = '';
        $this->pagoDemas    = false;
    }

    public function dejarQuePagueRival(): void
    {
        $r = Reserva::find($this->reservaId);
        if ($r && $r->estado === 'DRAFT') {
            $r->update(['estado' => 'PENDING']);
        }
        $this->redirect(route('agenda'));
    }

    public function pausarReserva(string $destino = ''): void
    {
        $r = Reserva::find($this->reservaId);
        if ($r && $r->estado === 'DRAFT' && Auth::id() === $r->creador_id) {
            $r->update(['estado' => 'PENDING']);
        }

        $url = (str_starts_with($destino, '/') && !str_starts_with($destino, '//'))
            ? $destino
            : route('agenda');

        $this->redirect($url);
    }

    public function cancelarYVolver(): void
    {
        $r = Reserva::find($this->reservaId);
        if ($r && Auth::id() === $r->creador_id) {
            $r->delete(); // cascade elimina los pagos
        }
        $this->redirect(route('agenda'));
    }

    public function enviarComprobante(): void
    {
        $this->validate([
            'comprobante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'comprobante.required' => 'Seleccioná un archivo.',
            'comprobante.mimes'    => 'Solo se aceptan JPG, PNG o PDF.',
            'comprobante.max'      => 'El archivo no puede superar 5 MB.',
        ]);

        $r = Reserva::find($this->reservaId);
        if (!$r) return;

        $miPago = PagoModel::find($this->miPagoId);
        if (!$miPago) return;

        $path      = $this->comprobante->store('comprobantes', 'public');
        $rutaLocal = Storage::disk('public')->path($path);
        $config    = Configuracion::getConfig();

        $verificacion = app(ComprobanteVerificador::class)->verificar(
            $rutaLocal,
            $this->totalReserva,    // siempre verificar contra el total completo
            $config->payment_alias  ?? '',
            $config->payment_cbu    ?? '',
            $config->payment_cuenta ?? '',
            $config->payment_cuit   ?? '',
            null,
            $config->payment_window_minutes ?? 30
        );

        // Rechazar si no es comprobante válido
        if (($verificacion['es_comprobante'] ?? null) !== true) {
            Storage::disk('public')->delete($path);
            $this->errorImporte = "El archivo adjunto no es un comprobante de transferencia bancaria válido o la transferencia no está completada. Por favor adjuntá el comprobante correcto.";
            $this->comprobante = null;
            return;
        }

        // Analizar importe: acepta pago total O pago parcial (solo su cuota, sin invitados)
        // Normalizar importe argentino: "$ 10.000,00" → 10000.00
        $importeRaw = preg_replace('/[^\d,.]/', '', $verificacion['importe_encontrado'] ?? '0');
        $importeRaw = str_replace('.', '', $importeRaw);   // eliminar puntos de miles
        $importeRaw = str_replace(',', '.', $importeRaw);  // coma decimal → punto
        $importeNum = (float) $importeRaw;
        $pagoCompleto = ($verificacion['importe_ok'] ?? null) === true;
        $pagoParcial  = !$pagoCompleto
            && !$this->hayInvitados
            && $importeNum > 0
            && abs($importeNum - $miPago->monto) < 1;

        if (!$pagoCompleto && !$pagoParcial) {
            Storage::disk('public')->delete($path);
            $importeEsperado = '$' . number_format($this->totalReserva, 0, ',', '.');
            $encontrado      = $verificacion['importe_encontrado'] ? ' (encontrado: ' . $verificacion['importe_encontrado'] . ')' : '';
            $importeParteStr    = '$' . number_format($miPago->monto, 0, ',', '.');
            $this->errorImporte = "El importe del comprobante no coincide. Se esperaba {$importeEsperado} (total) o {$importeParteStr} (tu parte){$encontrado}.";
            $this->pagoDemas    = $importeNum > 0 && $importeNum > $this->totalReserva;
            $this->comprobante  = null;
            return;
        }

        // Fecha incorrecta
        if (($verificacion['fecha_ok'] ?? null) !== true) {
            Storage::disk('public')->delete($path);
            $fechaEncontrada    = $verificacion['fecha_encontrada'] ? ' (fecha encontrada: ' . $verificacion['fecha_encontrada'] . ')' : '';
            $this->errorImporte = "La fecha del comprobante no corresponde al día de la reserva{$fechaEncontrada}. El pago debe realizarse el mismo día.";
            $this->comprobante  = null;
            return;
        }

        // Hora fuera de rango
        if (($verificacion['hora_ok'] ?? null) === false) {
            Storage::disk('public')->delete($path);
            $horaEncontrada     = $verificacion['hora_encontrada'] ? ' (hora encontrada: ' . $verificacion['hora_encontrada'] . ')' : '';
            $ventana = $config->payment_window_minutes ?? 30;
            $this->errorImporte = "El horario del comprobante está fuera del rango permitido{$horaEncontrada}. El pago debe realizarse al momento de la reserva o hasta {$ventana} minutos antes.";
            $this->comprobante  = null;
            return;
        }

        // Alias/CBU incorrecto
        if (($verificacion['alias_ok'] ?? null) === false) {
            Storage::disk('public')->delete($path);
            $aliasEncontrado    = $verificacion['alias_encontrado'] ? ' (encontrado: ' . $verificacion['alias_encontrado'] . ')' : '';
            $this->errorImporte = "El alias o CBU/CVU del comprobante no coincide con la cuenta del club{$aliasEncontrado}. Verificá que hayas transferido a la cuenta correcta.";
            $this->comprobante  = null;
            return;
        }

        $this->errorImporte = '';

        // Si es pago parcial, recalcular valido ignorando importe_ok
        // (ya confirmamos que el importe coincide con la cuota individual)
        if ($pagoParcial) {
            $valido = ($verificacion['es_comprobante'] ?? false) === true
                && ($verificacion['fecha_ok']  ?? null) === true
                && ($verificacion['hora_ok']   ?? null) !== false
                && ($verificacion['alias_ok']  ?? null) === true;
        } else {
            $valido = $verificacion['valido'] ?? false;
        }

        $estadoPago = $valido ? 'AUTHORIZED' : 'PENDING_REVIEW';

        $miPago->update([
            'comprobante'     => $path,
            'verificacion_ia' => $verificacion,
            'estado'          => $estadoPago,
        ]);

        // Si pagó el total completo (no solo su parte), autorizar también los otros pagos pendientes
        if ($valido && $pagoCompleto && !$this->hayInvitados) {
            PagoModel::where('reserva_id', $r->id)
                ->where('id', '!=', $miPago->id)
                ->where('estado', 'PENDIENTE')
                ->update(['estado' => 'AUTHORIZED', 'comprobante' => $path]);
        }

        $this->verificacion = $verificacion;
        $this->miPagoEstado = $estadoPago;
        $this->enviado      = true;

        // Actualizar estado de la reserva
        if ($valido) {
            $pendientes = PagoModel::where('reserva_id', $r->id)
                ->where('estado', 'PENDIENTE')
                ->count();

            if ($pendientes === 0) {
                $r->update(['estado' => 'AUTHORIZED', 'esta_pagado' => true]);
                $msg = $pagoCompleto && !$this->hayInvitados && $this->totalReserva > $this->totalAPagar
                    ? '¡Pago total verificado! Tu reserva fue confirmada.'
                    : '¡Pago verificado! Tu reserva fue confirmada.';
            } else {
                $r->update(['estado' => 'PARTIAL_PAYMENT']);
                $msg = '¡Tu parte fue verificada! Falta que tu/s rival/es abonen la suya.';
            }

            $this->dispatch('toast', message: $msg, type: 'success');
            $this->redirect(route('agenda'));
            return;
        } else {
            $pendientes  = PagoModel::where('reserva_id', $r->id)
                ->where('estado', 'PENDIENTE')
                ->count();
            $nuevoEstado = $pendientes === 0 ? 'PENDING_REVIEW' : 'PARTIAL_PAYMENT';
            $r->update(['estado' => $nuevoEstado]);
            $this->turno_estado = $nuevoEstado;
            $this->dispatch('toast', message: 'Comprobante enviado. El club lo revisará manualmente.', type: 'info');
        }
    }

    public function render()
    {
        return view('livewire.pago', [
            'config' => Configuracion::getConfig(),
        ])->layout('layouts.app');
    }
}
