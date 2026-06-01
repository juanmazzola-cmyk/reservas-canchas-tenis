<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class EscanearCarnet extends Component
{
    public function mount(): void
    {
        $rol = Auth::user()->rol;
        if (!in_array($rol, ['control', 'admin'])) {
            $this->redirect(route('agenda'));
        }
    }

    public function render()
    {
        return view('livewire.escanear-carnet')->layout('layouts.app');
    }
}
