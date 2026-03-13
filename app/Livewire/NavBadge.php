<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Reserva;
use Illuminate\Support\Facades\Auth;

class NavBadge extends Component
{
    public int $pendientes = 0;

    public function mount(): void
    {
        $this->refresh();
    }

    #[On('reservaAutorizada')]
    public function refresh(): void
    {
        if (Auth::user()?->rol !== 'admin') {
            $this->pendientes = 0;
            return;
        }
        $this->pendientes = Reserva::where('estado', 'PENDING_REVIEW')
            ->where('esta_pagado', false)
            ->count();
    }

    public function render()
    {
        return view('livewire.nav-badge');
    }
}
