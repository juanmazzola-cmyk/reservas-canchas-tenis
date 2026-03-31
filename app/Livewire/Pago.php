<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Configuracion;
use App\Services\ComprobanteVerificador;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Pago extends Component
{
    use WithFileUploads;

    public int $reservaId = 0;
    public array $jugadores = [];
    public int $cantNoSocios = 0;
    public float $totalAPagar = 0;
    public $comprobante;
    public bool $enviado = false;
    public string $waUrl = '';
    public ?array $verificacion = null;
    public string $errorImporte = '';
    public bool $pagoDemas = false;

    // datos del turno para mostrar en la vista
    public string $turno_dia = '';
    public string $turno_hora = '';
    public int $turno_cancha = 0;
    public ?string $turno_comprobante = null;
    public string $turno_estado = '';
    public ?string $turno_mp_status = null;
    public string $mp_result = '';

    public function mount(int $reserva): void
    {
        $r = Reserva::find($reserva);

        if (!$r) {
            $this->redirect(route('agenda'));
            return;
        }

        if (Auth::id() !== $r->creador_id && Auth::user()->rol !== 'admin') {
            $this->redirect(route('agenda'));
            return;
        }

        $this->reservaId        = $r->id;
        $this->turno_dia        = $r->dia;
        $this->turno_hora       = $r->hora;
        $this->turno_cancha     = $r->cancha_id;
        $this->turno_comprobante = $r->comprobante;
        $this->turno_estado     = $r->estado;
        $this->turno_mp_status  = $r->mp_status;
        $this->mp_result        = session('mp_result', '');

        $config = Configuracion::getConfig();
        $precio = (float) ($config->non_member_price ?? 0);

        $this->jugadores = User::whereIn('id', $r->jugadores_ids ?? [])
            ->get()
            ->map(fn($u) => [
                'nombre'      => $u->nombre . ' ' . $u->apellido,
                'es_socio'    => $u->es_socio,
                'es_invitado' => false,
            ])
            ->toArray();

        // Agregar invitados como no-socios
        foreach ($r->invitados ?? [] as $inv) {
            $this->jugadores[] = [
                'nombre'      => $inv['apellido'],
                'es_socio'    => false,
                'es_invitado' => true,
            ];
        }

        $this->cantNoSocios = collect($this->jugadores)->where('es_socio', false)->count();
        $this->totalAPagar  = $this->cantNoSocios * $precio;

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

    public function cancelarYVolver(): void
    {
        $r = Reserva::find($this->reservaId);
        if ($r && Auth::id() === $r->creador_id) {
            $r->delete();
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

        $path      = $this->comprobante->store('comprobantes', 'public');
        $rutaLocal = Storage::disk('public')->path($path);
        $config    = Configuracion::getConfig();

        $verificacion = app(ComprobanteVerificador::class)->verificar(
            $rutaLocal,
            $this->totalAPagar,
            $config->payment_alias  ?? '',
            $config->payment_cbu    ?? '',
            $config->payment_cuenta ?? '',
            $config->payment_cuit   ?? ''
        );

        // Si no es un comprobante bancario → error al usuario, no se guarda nada
        if (($verificacion['es_comprobante'] ?? null) === false) {
            Storage::disk('public')->delete($path);
            $this->errorImporte = "El archivo adjunto no es un comprobante de transferencia bancaria válido. Por favor adjuntá el comprobante correcto.";
            $this->comprobante = null;
            return;
        }

        // Si el importe no coincide → error al usuario, no se guarda nada
        if (($verificacion['importe_ok'] ?? null) === false) {
            Storage::disk('public')->delete($path);
            $importeEsperado = '$' . number_format($this->totalAPagar, 0, ',', '.');
            $encontrado = $verificacion['importe_encontrado'] ? ' (encontrado: ' . $verificacion['importe_encontrado'] . ')' : '';
            $this->errorImporte = "El importe del comprobante no coincide con el monto a pagar de {$importeEsperado}{$encontrado}. Revisá que hayas transferido el monto correcto.";
            // Detectar si pagó de más
            $importeTexto = $verificacion['importe_encontrado'] ?? '';
            $importeNumerico = (float) preg_replace('/[^\d]/', '', $importeTexto);
            $this->pagoDemas = $importeNumerico > 0 && $importeNumerico > $this->totalAPagar;
            $this->comprobante = null;
            return;
        }

        // Si la fecha o la hora del pago están fuera del rango permitido → rechazar, no tiene sentido revisarlo
        if (($verificacion['fecha_ok'] ?? null) === false) {
            Storage::disk('public')->delete($path);
            $fechaEncontrada = $verificacion['fecha_encontrada'] ? ' (fecha encontrada: ' . $verificacion['fecha_encontrada'] . ')' : '';
            $this->errorImporte = "La fecha del comprobante no corresponde al día de la reserva{$fechaEncontrada}. El pago debe realizarse el mismo día.";
            $this->comprobante = null;
            return;
        }

        if (($verificacion['hora_ok'] ?? null) === false) {
            Storage::disk('public')->delete($path);
            $horaEncontrada = $verificacion['hora_encontrada'] ? ' (hora encontrada: ' . $verificacion['hora_encontrada'] . ')' : '';
            $this->errorImporte = "El horario del comprobante está fuera del rango permitido{$horaEncontrada}. El pago debe realizarse al momento de la reserva o hasta 30 minutos antes.";
            $this->comprobante = null;
            return;
        }

        $this->errorImporte = '';

        // Confirmación automática: fecha + hora + importe + alias/CBU encontrado y correcto
        $valido = $verificacion['valido'] ?? false;

        $estadoFinal = $valido ? 'AUTHORIZED' : 'PENDING_REVIEW';

        $r->update([
            'comprobante'     => $path,
            'verificacion_ia' => $verificacion,
            'estado'          => $estadoFinal,
            'esta_pagado'     => $valido,
        ]);

        $this->verificacion      = $verificacion;
        $this->turno_comprobante = $path;
        $this->turno_estado      = $estadoFinal;
        $this->enviado           = true;

        if ($valido) {
            $this->dispatch('toast', message: '¡Pago verificado! Tu reserva fue confirmada.', type: 'success');
        } else {
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
