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
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->string('rank_name',50)->nullable();
            $table->string('rank_lavel',50)->nullable();
            $table->string('rank_lavel_unq',20)->nullable();
            $table->decimal('min_invest',8,2)->nullable();
            $table->decimal('min_deposit',8,2)->nullable();
            $table->decimal('min_earning',8,2)->nullable();
            $table->string('description')->nullable();
            $table->text('rank_icon')->nullable();
            $table->string('driver')->nullable();
            $table->integer('sort_by')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
