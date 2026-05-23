<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'grupo_sanguineo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('grupo_sanguineo', 3)->nullable()->after('telefono');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'grupo_sanguineo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('grupo_sanguineo');
            });
        }
    }
};
