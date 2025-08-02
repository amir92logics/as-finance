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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('gateway_id')->nullable()->constrained('gateways');
            $table->string('order_number',60)->nullable();
            $table->string('first_name',60)->nullable();
            $table->string('last_name',60)->nullable();
            $table->string('email',60)->nullable();
            $table->string('phone',30)->nullable();
            $table->string('city',60)->nullable();
            $table->string('state',60)->nullable();
            $table->string('zip',10)->nullable();
            $table->string('address',60)->nullable();
            $table->foreignId('area_id')->nullable()->constrained('areas');
            $table->text('additional_information')->nullable();
            $table->string('coupon_code',60)->nullable();
            $table->decimal('discount',10,2)->nullable();
            $table->boolean('payment_status')->default(0);
            $table->tinyInteger('order_status')->default(0);
            $table->decimal('subtotal',10,2)->nullable();
            $table->decimal('delivery_charge',10,2)->nullable();
            $table->decimal('vat',10,2)->nullable();
            $table->decimal('total',10,2)->nullable();
            $table->string('transaction_id',60)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
