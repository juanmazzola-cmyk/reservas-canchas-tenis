<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Registro extends Component
{
    public string $nombre = '';
    public string $apellido = '';
    public string $dni = '';
    public string $telefono = '';
    public string $email = '';
    public string $password = '';
    public bool $es_socio = false;

    protected function rules(): array
    {
        return [
            'nombre'   => 'required|string|min:2|max:100',
            'apellido' => 'required|string|min:2|max:100',
            'dni'      => 'required|string|max:20|unique:users,dni',
            'telefono' => ['required', 'string', 'max:20', 'regex:/\d{10,}/'],
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ];
    }

    protected array $messages = [
        'nombre.required'   => 'El nombre es obligatorio.',
        'apellido.required' => 'El apellido es obligatorio.',
        'dni.required'      => 'El DNI es obligatorio.',
        'dni.unique'        => 'Ya existe una cuenta con ese DNI.',
        'telefono.required' => 'El teléfono es obligatorio.',
        'telefono.regex'    => 'El teléfono debe tener al menos 10 dígitos.',
        'email.required'    => 'El email es obligatorio.',
        'email.email'       => 'El email no es válido.',
        'email.unique'      => 'Ya existe una cuenta con ese email.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
    ];

    public function registrar(): void
    {
        $this->validate();

        $user = User::create([
            'nombre'   => ucfirst(strtolower(trim($this->nombre))),
            'apellido' => ucfirst(strtolower(trim($this->apellido))),
            'dni'      => trim($this->dni),
            'telefono' => trim($this->telefono),
            'email'    => strtolower(trim($this->email)),
            'password' => Hash::make($this->password),
            'es_socio' => $this->es_socio,
            'rol'      => 'usuario',
        ]);

        Auth::login($user);
        session()->regenerate();
        session()->save();

        $this->redirect(route('agenda'), navigate: false);
    }

    public function render()
    {
        return view('livewire.registro')
            ->layout('layouts.guest');
    }
}
