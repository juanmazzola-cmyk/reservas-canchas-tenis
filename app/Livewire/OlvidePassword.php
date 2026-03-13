<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Hash;

class OlvidePassword extends Component
{
    public string $email = '';
    public int $paso = 1; // 1=form, 2=enviado
    public string $waUrl = '';
    public string $nombreUsuario = '';

    public function enviar(): void
    {
        $this->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Ingresá tu email.',
            'email.email'    => 'El email no es válido.',
        ]);

        $user = User::where('email', strtolower(trim($this->email)))->first();

        if (!$user) {
            $this->addError('email', 'No encontramos una cuenta con ese email.');
            return;
        }

        if (!$user->telefono) {
            $this->addError('email', 'Tu cuenta no tiene teléfono registrado. Contactá al administrador.');
            return;
        }

        // Generar código de 6 dígitos
        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Guardar como contraseña temporal
        $user->update([
            'password'             => Hash::make($codigo),
            'must_change_password' => true,
        ]);

        // Construir URL de WhatsApp al teléfono del usuario
        $tel = preg_replace('/\D/', '', $user->telefono);
        $config = Configuracion::getConfig();
        $clubNombre = $config->club_name ?? 'el club';
        $msg = urlencode(
            "🎾 *Recuperación de contraseña - {$clubNombre}*\n\n" .
            "Tu contraseña temporal es:\n\n" .
            "*{$codigo}*\n\n" .
            "Ingresá con este código y luego cambiala desde tu perfil."
        );
        $this->waUrl        = "https://wa.me/{$tel}?text={$msg}";
        $this->nombreUsuario = $user->nombre;
        $this->paso         = 2;
    }

    public function render()
    {
        return view('livewire.olvide-password')
            ->layout('layouts.guest');
    }
}
