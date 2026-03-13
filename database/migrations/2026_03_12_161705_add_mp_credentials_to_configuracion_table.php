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
        Schema::table('configuracion', function (Blueprint $table) {
            $table->string('mp_access_token')->nullable()->after('notification_text');
            $table->string('mp_public_key')->nullable()->after('mp_access_token');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn(['mp_access_token', 'mp_public_key']);
        });
    }
};
