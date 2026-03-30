<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Configuracion;
use App\Services\ComprobanteVerificador;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.app')]
class PruebaComprobante extends Component
{
    use WithFileUploads;

    public $comprobante;
    public string $importe = '';
    public string $alias = '';
    public string $cbu = '';
    public string $cuenta = '';
    public string $cuit = '';
    public string $fechaHoraSimulada = '';  // formato: Y-m-d\TH:i
    public ?array $resultado = null;
    public bool $verificando = false;

    public function mount(): void
    {
        if (Auth::user()->rol !== 'admin') {
            $this->redirect(route('agenda'));
            return;
        }

        $config = Configuracion::getConfig();
        $this->importe  = (string) ($config->non_member_price ?? '');
        $this->alias    = $config->payment_alias ?? '';
        $this->cbu      = $config->payment_cbu ?? '';
        $this->cuenta   = $config->payment_cuenta ?? '';
        $this->cuit     = $config->payment_cuit ?? '';
        $this->fechaHoraSimulada = now()->format('Y-m-d\TH:i');
    }

    public function verificar(): void
    {
        $this->validate([
            'comprobante'       => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'importe'           => 'required|numeric|min:1',
            'fechaHoraSimulada' => 'required',
        ], [
            'comprobante.required' => 'Subí un comprobante.',
            'importe.required'     => 'Ingresá el importe esperado.',
            'fechaHoraSimulada.required' => 'Ingresá la fecha y hora del comprobante.',
        ]);

        $path      = $this->comprobante->store('pruebas-ia', 'public');
        $rutaLocal = Storage::disk('public')->path($path);

        $fechaHoraBase = Carbon::parse($this->fechaHoraSimulada);

        $this->resultado = app(ComprobanteVerificador::class)->verificar(
            $rutaLocal,
            (float) $this->importe,
            $this->alias,
            $this->cbu,
            $this->cuenta,
            $this->cuit,
            $fechaHoraBase
        );

        // Eliminar archivo temporal de prueba
        Storage::disk('public')->delete($path);
    }

    public function limpiar(): void
    {
        $this->resultado   = null;
        $this->comprobante = null;
    }

    public function render()
    {
        return view('livewire.admin.prueba-comprobante');
    }
}
