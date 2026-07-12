<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            if (!Schema::hasIndex('reservas', ['dia', 'cancha_id', 'hora'])) {
                $table->index(['dia', 'cancha_id', 'hora']);
            }
            if (!Schema::hasIndex('reservas', ['estado'])) {
                $table->index('estado');
            }
            if (!Schema::hasIndex('reservas', ['creador_id'])) {
                $table->index('creador_id');
            }
        });

        Schema::table('pagos', function (Blueprint $table) {
            if (!Schema::hasIndex('pagos', ['reserva_id', 'estado'])) {
                $table->index(['reserva_id', 'estado']);
            }
            if (!Schema::hasIndex('pagos', ['estado_autorizacion', 'updated_at'])) {
                $table->index(['estado_autorizacion', 'updated_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            if (Schema::hasIndex('reservas', ['dia', 'cancha_id', 'hora'])) {
                $table->dropIndex(['dia', 'cancha_id', 'hora']);
            }
            if (Schema::hasIndex('reservas', ['estado'])) {
                $table->dropIndex(['estado']);
            }
            if (Schema::hasIndex('reservas', ['creador_id'])) {
                $table->dropIndex(['creador_id']);
            }
        });

        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasIndex('pagos', ['reserva_id', 'estado'])) {
                $table->dropIndex(['reserva_id', 'estado']);
            }
            if (Schema::hasIndex('pagos', ['estado_autorizacion', 'updated_at'])) {
                $table->dropIndex(['estado_autorizacion', 'updated_at']);
            }
        });
    }
};
