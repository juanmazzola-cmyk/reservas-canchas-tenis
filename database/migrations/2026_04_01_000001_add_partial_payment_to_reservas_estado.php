<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE reservas MODIFY COLUMN estado ENUM('PENDING','PENDING_REVIEW','AUTHORIZED','REJECTED','SUSPENDIDA','PARTIAL_PAYMENT') DEFAULT 'PENDING'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE reservas MODIFY COLUMN estado ENUM('PENDING','PENDING_REVIEW','AUTHORIZED','REJECTED','SUSPENDIDA') DEFAULT 'PENDING'");
    }
};
