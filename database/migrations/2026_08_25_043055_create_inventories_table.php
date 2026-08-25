<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('damaged_quantity')->default(0);
            $table->integer('wasted_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(10);
            $table->boolean('is_in_stock')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
