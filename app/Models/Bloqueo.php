<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bloqueo extends Model
{
    protected $table = 'bloqueos';

    protected $fillable = [
        'dia',
        'hora',
        'cancha_id',
        'razon',
    ];
}
