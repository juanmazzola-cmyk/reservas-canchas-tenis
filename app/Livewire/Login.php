<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuracion;

class Login extends Component
{
    public string $dni = '';
    public string $password = '';
    public bool $showPassword = false;

    public function login(): void
    {
        $this->validate([
            'dni'      => 'required',
            'password' => 'required',
        ], [
            'dni.required'      => 'El DNI es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $user = \App\Models\User::where('dni', trim($this->dni))->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($this->password, $user->password)) {
            $this->addError('dni', 'DNI o contraseña incorrectos.');
            return;
        }

        Auth::login($user);
        session()->regenerate();
        session()->save();
        $this->redirect(route('agenda'), navigate: false);
    }

    public function render()
    {
        $clubName = Configuracion::first()?->club_name ?? 'Liga Padres Tenis';
        return view('livewire.login', compact('clubName'))
            ->layout('layouts.guest');
    }
}
