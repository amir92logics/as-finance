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
        Schema::create('shipping_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->references('id')->on('areas')->onDelete('cascade');
            $table->string('order_from');
            $table->string('order_to')->nullable();
            $table->decimal('delivery_charge',10,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_charges');
    }
};
