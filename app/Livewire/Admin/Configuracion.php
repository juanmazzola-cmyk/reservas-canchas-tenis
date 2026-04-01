<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Configuracion as ConfigModel;
use App\Models\Reserva;
use App\Models\Bloqueo;
use Carbon\Carbon;

#[Layout('layouts.app')]
class Configuracion extends Component
{
    public string $club_name = '';
    public string $club_address = '';
    public string $club_lat = '';
    public string $club_lng = '';
    public int $court_count = 4;
    public array $cancha_names = [];
    public array $horarios = [];
    public string $non_member_price = '7500';
    public string $payment_alias = '';
    public string $payment_cbu = '';
    public string $payment_cuenta = '';
    public string $payment_cuit = '';
    public string $payment_link = '';
    public string $payment_instructions = '';
    public int $advance_booking_limit_hours = 96;
    public int $payment_window_minutes = 30;
    public string $admin_whatsapp = '';
    public string $announcement_text = '';
    public bool $announcement_enabled = false;
    public string $notification_text = '';
    public string $mp_access_token = '';
    public string $mp_public_key = '';
    public string $anthropic_credits_date = '';

    public string $nuevoSlot = '';

    public function mount(): void
    {
        $config = ConfigModel::getConfig();

        $this->club_name                    = $config->club_name;
        $this->club_address                 = $config->club_address ?? '';
        $this->club_lat                     = $config->club_lat ? (string) $config->club_lat : '';
        $this->club_lng                     = $config->club_lng ? (string) $config->club_lng : '';
        $this->court_count                  = $config->court_count;
        $this->cancha_names                 = $config->cancha_names ?? [];
        $this->horarios                     = $config->slots ?? [];
        $this->non_member_price             = (string) $config->non_member_price;
        $this->payment_alias                = $config->payment_alias ?? '';
        $this->payment_cbu                  = $config->payment_cbu ?? '';
        $this->payment_cuenta               = $config->payment_cuenta ?? '';
        $this->payment_cuit                 = $config->payment_cuit ?? '';
        $this->payment_link                 = $config->payment_link ?? '';
        $this->payment_instructions         = $config->payment_instructions ?? '';
        $this->advance_booking_limit_hours  = $config->advance_booking_limit_hours;
        $this->payment_window_minutes       = $config->payment_window_minutes ?? 30;
        $this->admin_whatsapp               = $config->admin_whatsapp ?? '';
        $this->announcement_text            = $config->announcement_text ?? '';
        $this->announcement_enabled         = $config->announcement_enabled;
        $this->notification_text            = $config->notification_text ?? '';
        $this->mp_access_token              = $config->mp_access_token ?? '';
        $this->mp_public_key                = $config->mp_public_key ?? '';
        $this->anthropic_credits_date       = $config->anthropic_credits_date
            ? $config->anthropic_credits_date->format('Y-m-d')
            : '';
    }

    private function getProximosDias(): array
    {
        $etiquetas = ['Hoy', 'Mañana', 'Pasado', 'En 3 días', 'En 4 días'];
        $result = [];

        for ($i = 0; $i <= 4; $i++) {
            $fecha   = Carbon::today()->addDays($i)->toDateString();
            $dia     = $this->fechaADia($fecha);
            $bloqueo = Bloqueo::where('dia', $dia)->whereNull('hora')->whereNull('cancha_id')->first();

            $result[] = [
                'index'     => $i,
                'fecha'     => $fecha,
                'etiqueta'  => $etiquetas[$i],
                'bloqueado' => (bool) $bloqueo,
                'razon'     => $bloqueo ? ($bloqueo->razon ?? '') : '',
            ];
        }

        return $result;
    }

    private function fechaADia(string $fecha): string
    {
        $f = Carbon::parse($fecha);
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $diaSemana = $dias[$f->dayOfWeek];
        return strtolower($diaSemana) . ' ' . $f->format('d') . ' ' . strtolower($meses[$f->month]);
    }

    public function guardarMotivo(string $fecha, string $motivo = ''): void
    {
        $dia     = $this->fechaADia($fecha);
        $bloqueo = Bloqueo::where('dia', $dia)->whereNull('hora')->whereNull('cancha_id')->first();
        if ($bloqueo) {
            $bloqueo->update(['razon' => $motivo ?: 'Día bloqueado completo']);
            $this->dispatch('toast', message: 'Motivo guardado.', type: 'success');
        }
    }

    public function toggleBloqueo(string $fecha): void
    {
        $dia    = $this->fechaADia($fecha);
        $existe = Bloqueo::where('dia', $dia)->whereNull('hora')->whereNull('cancha_id')->first();

        if ($existe) {
            $existe->delete();
            $this->dispatch('toast', message: 'Día desbloqueado.', type: 'success');
        } else {
            Bloqueo::where('dia', $dia)->delete();
            Bloqueo::create(['dia' => $dia, 'hora' => null, 'cancha_id' => null, 'razon' => 'Día bloqueado completo']);
            $this->dispatch('toast', message: 'Día bloqueado.', type: 'success');
        }
    }

    public function agregarSlot(): void
    {
        $slot = trim($this->nuevoSlot);
        if (!preg_match('/^\d{2}:\d{2}$/', $slot)) {
            $this->addError('nuevoSlot', 'Formato inválido. Usá HH:MM (ej: 08:00)');
            return;
        }

        if (in_array($slot, $this->horarios)) {
            $this->addError('nuevoSlot', 'Ese horario ya existe.');
            return;
        }

        $this->horarios[] = $slot;
        sort($this->horarios);
        $this->nuevoSlot = '';
    }

    public function quitarSlot(string $slot): void
    {
        $this->horarios = array_values(array_filter($this->horarios, fn($s) => $s !== $slot));
    }

    public function guardar(): void
    {
        $this->validate([
            'club_name'                   => 'required|string|max:100',
            'court_count'                 => 'required|integer|min:1|max:20',
            'non_member_price'            => 'required|numeric|min:0',
            'advance_booking_limit_hours' => 'required|integer|min:1|max:720',
            'payment_window_minutes'      => 'required|integer|min:5|max:1440',
            'admin_whatsapp'              => 'nullable|string|max:30',
            'payment_alias'               => 'nullable|string|max:100',
            'payment_link'                => 'nullable|url|max:500',
        ], [
            'club_name.required'                   => 'El nombre del club es obligatorio.',
            'court_count.required'                 => 'La cantidad de canchas es obligatoria.',
            'non_member_price.required'            => 'El precio para no socios es obligatorio.',
            'advance_booking_limit_hours.required' => 'Las horas de anticipación son obligatorias.',
            'payment_link.url'                     => 'El link de pago debe ser una URL válida.',
        ]);

        $config = ConfigModel::getConfig();
        $config->update([
            'club_name'                    => $this->club_name,
            'club_address'                 => $this->club_address ?: null,
            'club_lat'                     => $this->club_lat !== '' ? (float) $this->club_lat : null,
            'club_lng'                     => $this->club_lng !== '' ? (float) $this->club_lng : null,
            'court_count'                  => $this->court_count,
            'cancha_names'                 => $this->cancha_names,
            'slots'                        => $this->horarios,
            'non_member_price'             => (float) $this->non_member_price,
            'payment_alias'                => $this->payment_alias ?: null,
            'payment_cbu'                  => $this->payment_cbu ?: null,
            'payment_cuenta'               => $this->payment_cuenta ?: null,
            'payment_cuit'                 => $this->payment_cuit ?: null,
            'payment_link'                 => $this->payment_link ?: null,
            'payment_instructions'         => $this->payment_instructions ?: null,
            'advance_booking_limit_hours'  => $this->advance_booking_limit_hours,
            'payment_window_minutes'       => $this->payment_window_minutes,
            'admin_whatsapp'               => $this->admin_whatsapp ?: null,
            'announcement_text'            => $this->announcement_text ?: null,
            'announcement_enabled'         => $this->announcement_enabled,
            'notification_text'            => $this->notification_text ?: null,
            'mp_access_token'              => $this->mp_access_token ?: null,
            'mp_public_key'                => $this->mp_public_key ?: null,
            'anthropic_credits_date'       => $this->anthropic_credits_date ?: null,
        ]);

        $this->dispatch('toast', message: 'Configuración guardada correctamente.', type: 'success');
    }

    private function getStatsVerificacionIA(): array
    {
        $total         = Reserva::whereNotNull('verificacion_ia')->count();
        $confirmadas   = Reserva::whereNotNull('verificacion_ia')
            ->whereRaw("JSON_EXTRACT(verificacion_ia, '$.valido') = true")
            ->count();
        $revision      = $total - $confirmadas;
        $costoEstimado = round($total * 0.002, 3);

        $diasRestantes  = null;
        $vencimiento    = null;
        $alertaVencimiento = false;

        $config = ConfigModel::getConfig();
        if ($config->anthropic_credits_date) {
            $vencimiento    = $config->anthropic_credits_date->addYear();
            $diasRestantes  = (int) now()->startOfDay()->diffInDays($vencimiento, false);
            $alertaVencimiento = $diasRestantes <= 5;
        }

        return compact('total', 'confirmadas', 'revision', 'costoEstimado', 'diasRestantes', 'vencimiento', 'alertaVencimiento');
    }

    public function render()
    {
        return view('livewire.admin.configuracion', [
            'proximosDias'        => $this->getProximosDias(),
            'usuariosConTelefono' => \App\Models\User::whereNotNull('telefono')
                ->where('telefono', '!=', '')
                ->orderBy('apellido')
                ->get(['id', 'nombre', 'apellido', 'telefono'])
                ->toArray(),
            'statsIA'             => $this->getStatsVerificacionIA(),
        ]);
    }
}
