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
        if (!Schema::hasColumn('users', 'foto_carnet')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('foto_carnet')->nullable()->after('grupo_sanguineo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'foto_carnet')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('foto_carnet');
            });
        }
    }
};
