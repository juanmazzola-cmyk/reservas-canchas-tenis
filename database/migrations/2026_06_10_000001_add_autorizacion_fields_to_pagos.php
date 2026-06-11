<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos', 'estado_autorizacion')) {
                $table->enum('estado_autorizacion', ['aprobado_ia', 'rechazado_ia', 'pendiente_admin', 'aprobado_admin'])
                      ->default('aprobado_ia')
                      ->after('estado');
            }
            if (!Schema::hasColumn('pagos', 'motivo_rechazo')) {
                $table->text('motivo_rechazo')->nullable()->after('estado_autorizacion');
            }
            if (!Schema::hasColumn('pagos', 'autorizado_por')) {
                $table->unsignedBigInteger('autorizado_por')->nullable()->after('motivo_rechazo');
            }
            if (!Schema::hasColumn('pagos', 'autorizado_at')) {
                $table->timestamp('autorizado_at')->nullable()->after('autorizado_por');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            foreach (['autorizado_at', 'autorizado_por', 'motivo_rechazo', 'estado_autorizacion'] as $col) {
                if (Schema::hasColumn('pagos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
