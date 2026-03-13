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
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('mp_preference_id')->nullable()->after('comprobante');
            $table->string('mp_payment_id')->nullable()->after('mp_preference_id');
            $table->string('mp_status')->nullable()->after('mp_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['mp_preference_id', 'mp_payment_id', 'mp_status']);
        });
    }
};
