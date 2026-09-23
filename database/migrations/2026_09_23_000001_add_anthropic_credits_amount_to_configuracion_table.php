<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('configuracion', 'anthropic_credits_amount')) {
            Schema::table('configuracion', function (Blueprint $table) {
                $table->decimal('anthropic_credits_amount', 8, 2)->nullable()->after('anthropic_credits_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('configuracion', 'anthropic_credits_amount')) {
            Schema::table('configuracion', function (Blueprint $table) {
                $table->dropColumn('anthropic_credits_amount');
            });
        }
    }
};
