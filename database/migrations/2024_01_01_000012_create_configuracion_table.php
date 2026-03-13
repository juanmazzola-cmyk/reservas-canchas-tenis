<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('club_name')->default('Liga Padres Tenis');
            $table->integer('court_count')->default(4);
            $table->json('slots');
            $table->decimal('non_member_price', 10, 2)->default(7500);
            $table->string('payment_alias')->nullable();
            $table->string('payment_link')->nullable();
            $table->text('payment_instructions')->nullable();
            $table->integer('advance_booking_limit_hours')->default(96);
            $table->string('admin_whatsapp')->nullable();
            $table->text('announcement_text')->nullable();
            $table->boolean('announcement_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
