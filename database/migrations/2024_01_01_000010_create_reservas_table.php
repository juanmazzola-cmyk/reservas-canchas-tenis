<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->string('dia');
            $table->string('hora');
            $table->integer('cancha_id');
            $table->json('jugadores_ids');
            $table->unsignedBigInteger('creador_id');
            $table->boolean('esta_pagado')->default(false);
            $table->text('comprobante')->nullable();
            $table->enum('estado', ['PENDING', 'AUTHORIZED', 'REJECTED'])->default('PENDING');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
