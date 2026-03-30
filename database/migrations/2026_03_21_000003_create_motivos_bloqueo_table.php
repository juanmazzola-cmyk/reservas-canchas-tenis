<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivos_bloqueo', function (Blueprint $table) {
            $table->id();
            $table->string('emoji', 10);
            $table->string('descripcion');
            $table->timestamps();
        });

        DB::table('motivos_bloqueo')->insert([
            ['emoji' => '🌧', 'descripcion' => 'Lluvia',        'created_at' => now(), 'updated_at' => now()],
            ['emoji' => '🔧', 'descripcion' => 'Mantenimiento', 'created_at' => now(), 'updated_at' => now()],
            ['emoji' => '🏆', 'descripcion' => 'Torneo',        'created_at' => now(), 'updated_at' => now()],
            ['emoji' => '🔒', 'descripcion' => 'Club cerrado',  'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('motivos_bloqueo');
    }
};
