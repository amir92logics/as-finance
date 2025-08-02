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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('subcategory_id')->constrained('sub_categories');
            $table->decimal('price', 11, 2)->nullable();
            $table->string('page_title')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->longText('meta_description')->nullable();
            $table->string('meta_image')->nullable();
            $table->string('meta_image_Driver')->nullable();
            $table->enum('status', ['Available',  'Stock Out'])->default('Available');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
