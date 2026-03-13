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
        'creador_id',
        'esta_pagado',
        'comprobante',
        'estado',
        'mp_preference_id',
        'mp_payment_id',
        'mp_status',
    ];

    protected function casts(): array
    {
        return [
            'jugadores_ids' => 'array',
            'esta_pagado' => 'boolean',
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
}
