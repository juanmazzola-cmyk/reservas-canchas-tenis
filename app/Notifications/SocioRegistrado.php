<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class SocioRegistrado extends Notification
{
    public function __construct(public User $socio) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'user_id'   => $this->socio->id,
            'nombre'    => $this->socio->nombre . ' ' . $this->socio->apellido,
            'nro_socio' => $this->socio->nro_socio,
        ];
    }
}
