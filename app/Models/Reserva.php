<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'dia',
        'hora',
        'cancha_id',
        'jugadores_ids',
        'invitados',
        'creador_id',
        'esta_pagado',
        'comprobante',
        'verificacion_ia',
        'estado',
        'suspension_motivo',
        'mp_preference_id',
        'mp_payment_id',
        'mp_status',
    ];

    protected function casts(): array
    {
        return [
            'jugadores_ids'  => 'array',
            'invitados'      => 'array',
            'esta_pagado'    => 'boolean',
            'verificacion_ia' => 'array',
        ];
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    public function jugadores()
    {
        $ids = $this->jugadores_ids ?? [];
        return User::whereIn('id', $ids)->get();
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
