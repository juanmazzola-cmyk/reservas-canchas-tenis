<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Perfil extends Component
{
    public string $nombre = '';
    public string $apellido = '';
    public string $telefono = '';
    public string $email = '';

    public string $passwordActual = '';
    public string $passwordNuevo = '';
    public string $passwordConfirm = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->nombre   = $user->nombre;
        $this->apellido = $user->apellido;
        $this->telefono = $user->telefono ?? '';
        $this->email    = $user->email;
    }

    public function guardarPerfil(): void
    {
        $user = Auth::user();

        $this->validate([
            'nombre'   => 'required|string|min:2|max:100',
            'apellido' => 'required|string|min:2|max:100',
            'telefono' => 'nullable|string|max:20',
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'nombre.required'   => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required'    => 'El email es obligatorio.',
            'email.email'       => 'El email no es válido.',
            'email.unique'      => 'Ese email ya está en uso.',
        ]);

        $user->update([
            'nombre'   => trim($this->nombre),
            'apellido' => trim($this->apellido),
            'telefono' => trim($this->telefono),
            'email'    => strtolower(trim($this->email)),
        ]);

        $this->dispatch('toast', message: 'Perfil actualizado correctamente.', type: 'success');
    }

    public function cambiarPassword(): void
    {
        $user = Auth::user();

        if ($user->must_change_password) {
            $this->validate([
                'passwordNuevo'   => 'required|min:6',
                'passwordConfirm' => 'required|same:passwordNuevo',
            ], [
                'passwordNuevo.required'   => 'La nueva contraseña es obligatoria.',
                'passwordNuevo.min'        => 'Mínimo 6 caracteres.',
                'passwordConfirm.required' => 'Confirmá la nueva contraseña.',
                'passwordConfirm.same'     => 'Las contraseñas no coinciden.',
            ]);
        } else {
            $this->validate([
                'passwordActual'  => 'required',
                'passwordNuevo'   => 'required|min:6',
                'passwordConfirm' => 'required|same:passwordNuevo',
            ], [
                'passwordActual.required'  => 'Ingresá tu contraseña actual.',
                'passwordNuevo.required'   => 'La nueva contraseña es obligatoria.',
                'passwordNuevo.min'        => 'Mínimo 6 caracteres.',
                'passwordConfirm.required' => 'Confirmá la nueva contraseña.',
                'passwordConfirm.same'     => 'Las contraseñas no coinciden.',
            ]);

            if (!Hash::check($this->passwordActual, $user->password)) {
                $this->addError('passwordActual', 'La contraseña actual es incorrecta.');
                return;
            }
        }

        $user->update([
            'password'             => Hash::make($this->passwordNuevo),
            'must_change_password' => false,
        ]);

        $this->passwordActual  = '';
        $this->passwordNuevo   = '';
        $this->passwordConfirm = '';

        $this->dispatch('toast', message: 'Contraseña actualizada correctamente.', type: 'success');
        $this->dispatch('limpiarBannerPassword', userId: $user->id);
    }

    public function render()
    {
        return view('livewire.perfil')->layout('layouts.app');
    }
}
