<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('configuracion', 'carnet_enabled')) {
            Schema::table('configuracion', function (Blueprint $table) {
                $table->boolean('carnet_enabled')->default(true)->after('announcement_enabled');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('configuracion', 'carnet_enabled')) {
            Schema::table('configuracion', function (Blueprint $table) {
                $table->dropColumn('carnet_enabled');
            });
        }
    }
};
