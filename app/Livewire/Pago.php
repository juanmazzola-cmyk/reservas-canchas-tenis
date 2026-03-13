<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Auth;

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
                'nombre'   => $u->nombre . ' ' . $u->apellido,
                'es_socio' => $u->es_socio,
            ])
            ->toArray();

        $this->cantNoSocios = collect($this->jugadores)->where('es_socio', false)->count();
        $this->totalAPagar  = $this->cantNoSocios * $precio;

        // Pre-construir URL de WhatsApp para el admin
        if ($config->admin_whatsapp) {
            $tel    = preg_replace('/\D/', '', $config->admin_whatsapp);
            $lineas = collect($this->jugadores)
                ->map(fn($j) => '• ' . $j['nombre'] . ' (' . ($j['es_socio'] ? 'Socio' : 'No socio') . ')')
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

        $path = $this->comprobante->store('comprobantes', 'public');

        $r->update([
            'comprobante' => $path,
            'estado'      => 'PENDING_REVIEW',
        ]);

        $this->turno_comprobante = $path;
        $this->turno_estado      = 'PENDING_REVIEW';
        $this->enviado           = true;

        $this->dispatch('toast', message: 'Comprobante enviado. Aguardá la autorización del club.', type: 'success');
    }

    public function render()
    {
        return view('livewire.pago', [
            'config' => Configuracion::getConfig(),
        ])->layout('layouts.app');
    }
}
