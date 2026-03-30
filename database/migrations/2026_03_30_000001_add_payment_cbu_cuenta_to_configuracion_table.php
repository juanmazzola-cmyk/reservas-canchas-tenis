<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->string('payment_cbu', 30)->nullable()->after('payment_alias');
            $table->string('payment_cuenta', 50)->nullable()->after('payment_cbu');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn(['payment_cbu', 'payment_cuenta']);
        });
    }
};
