<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuracion;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $showPassword = false;

    public function login(): void
    {
        $this->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'El email es obligatorio.',
            'email.email'       => 'El email no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $credentials = [
            'email'    => strtolower(trim($this->email)),
            'password' => $this->password,
        ];

        if (Auth::attempt($credentials)) {
            session()->regenerate();
            session()->save();
            $this->redirect(route('agenda'), navigate: false);
            return;
        }

        $this->addError('email', 'Credenciales incorrectas.');
    }

    public function render()
    {
        $clubName = Configuracion::first()?->club_name ?? 'Liga Padres Tenis';
        return view('livewire.login', compact('clubName'))
            ->layout('layouts.guest');
    }
}
