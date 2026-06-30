<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Pago extends Model implements Auditable
{
    use AuditableTrait;
    protected $table = 'pagos';

    protected $fillable = [
        'reserva_id',
        'user_id',
        'monto',
        'comprobante',
        'verificacion_ia',
        'estado',
        'estado_autorizacion',
        'motivo_rechazo',
        'autorizado_por',
        'autorizado_at',
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
