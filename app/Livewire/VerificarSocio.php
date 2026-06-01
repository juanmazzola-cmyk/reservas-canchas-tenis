<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class VerificarSocio extends Component
{
    public ?User $socio = null;

    public function mount(int $id): void
    {
        $this->socio = User::find($id);
    }

    public function render()
    {
        return view('livewire.verificar-socio')->layout('layouts.app');
    }
}
