<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 05 — ইনভেন্টরি মডিউল:
     * - inventories: শুধুমাত্র variant-ভিত্তিক (product_variant_id NOT NULL + unique),
     *   allow_backorder যোগ; damaged/wasted/is_in_stock কলাম বাদ (লেজার = inventory_transactions)
     * - stock_adjustments: বাদ (inventory_transactions এর সাথে প্রতিস্থাপিত)
     * - inventory_transactions: সম্পূর্ণ স্টক-মুভমেন্ট লেজার
     */
    public function up(): void
    {
        // অনাথ/অবৈধ রেকর্ড থাকলে সরিয়ে ফেলা — NOT NULL + UNIQUE constraint-এর আগে
        DB::table('inventories')->whereNull('product_variant_id')->delete();

        Schema::table('inventories', function (Blueprint $table) {
            // কম্পোজিট ইউনিক ইনডেক্সটি দুটো FK-ই ব্যবহার করে — আগে FK, পরে ইনডেক্স
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_variant_id']);
            $table->dropUnique('inventories_product_id_product_variant_id_unique');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['product_id', 'damaged_quantity', 'wasted_quantity', 'is_in_stock']);
            $table->unsignedBigInteger('product_variant_id')->change();
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->foreign('product_variant_id')
                ->references('id')->on('product_variants')->cascadeOnDelete();
            $table->unique('product_variant_id');
            $table->boolean('allow_backorder')->default(false);
        });

        Schema::dropIfExists('stock_adjustments');

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_variant_id', 'created_at']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->integer('quantity');
            $table->integer('previous_quantity');
            $table->integer('new_quantity');
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropUnique(['product_variant_id']);
            $table->dropColumn('allow_backorder');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('product_variant_id')->nullable()->change();
            $table->unsignedBigInteger('product_id');
            $table->boolean('is_in_stock')->default(true);
            $table->integer('damaged_quantity')->default(0);
            $table->integer('wasted_quantity')->default(0);
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->foreign('product_variant_id')
                ->references('id')->on('product_variants')->nullOnDelete();
            $table->foreign('product_id')
                ->references('id')->on('products')->cascadeOnDelete();
            $table->unique(['product_id', 'product_variant_id']);
        });
    }
};
