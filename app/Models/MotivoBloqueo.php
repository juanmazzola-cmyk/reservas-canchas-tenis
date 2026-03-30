<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotivoBloqueo extends Model
{
    protected $table = 'motivos_bloqueo';
    protected $fillable = ['emoji', 'descripcion'];
}
