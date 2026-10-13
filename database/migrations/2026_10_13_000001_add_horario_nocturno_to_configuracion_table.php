<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('configuracion', 'horario_nocturno')) {
            Schema::table('configuracion', function (Blueprint $table) {
                $table->string('horario_nocturno', 5)->nullable()->after('slots');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('configuracion', 'horario_nocturno')) {
            Schema::table('configuracion', function (Blueprint $table) {
                $table->dropColumn('horario_nocturno');
            });
        }
    }
};
