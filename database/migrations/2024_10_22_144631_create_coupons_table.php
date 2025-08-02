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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('coupon_code')->unique();
            $table->decimal('minimum_order_price',10,2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('discount',10,2)->nullable();
            $table->enum('discount_type',['Fixed','Percent'])->default('Percent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
