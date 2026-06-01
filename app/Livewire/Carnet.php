<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Configuracion;

class Carnet extends Component
{
    use WithFileUploads;

    public $foto;
    public ?string $fotoUrl = null;

    public function mount(): void
    {
        $config = Configuracion::getConfig();

        if (!$config->carnet_enabled) {
            $this->redirect(route('agenda'));
            return;
        }

        $user = Auth::user();

        if (!$user->es_socio) {
            $this->redirect(route('agenda'));
            return;
        }

        $this->fotoUrl = $user->foto_carnet
            ? Storage::url($user->foto_carnet)
            : null;
    }

    public function updatedFoto(): void
    {
        $this->validate([
            'foto' => 'image|max:5120',
        ], [
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.max'   => 'La imagen no puede superar 5MB.',
        ]);

        $user = Auth::user();

        if ($user->foto_carnet) {
            Storage::delete($user->foto_carnet);
        }

        $path = $this->foto->store('fotos-carnet', 'public');
        $user->update(['foto_carnet' => $path]);

        $this->fotoUrl = Storage::url($path);
        $this->foto = null;

        $this->dispatch('toast', message: 'Foto actualizada.', type: 'success');
    }

    public function render()
    {
        return view('livewire.carnet', [
            'user' => Auth::user(),
        ])->layout('layouts.app');
    }
}
