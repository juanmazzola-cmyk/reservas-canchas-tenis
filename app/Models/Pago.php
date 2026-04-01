<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'reserva_id',
        'user_id',
        'monto',
        'comprobante',
        'verificacion_ia',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'monto'          => 'float',
            'verificacion_ia' => 'array',
        ];
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
