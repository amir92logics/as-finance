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
        Schema::table('basic_controls', function (Blueprint $table) {
            $table->boolean('registration_bonus')->default(true)->after('profit_commission');
            $table->decimal('registration_bonus_amount',10,2)->default(0)->after('registration_bonus');
            $table->decimal('referral_user_bonus_amount',10,2)->default(0)->after('registration_bonus_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('basic_controls', function (Blueprint $table) {
            //
        });
    }
};
