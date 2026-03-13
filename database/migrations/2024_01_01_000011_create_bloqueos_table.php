<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bloqueos', function (Blueprint $table) {
            $table->id();
            $table->string('dia');
            $table->string('hora')->nullable();
            $table->integer('cancha_id')->nullable();
            $table->string('razon')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueos');
    }
};
