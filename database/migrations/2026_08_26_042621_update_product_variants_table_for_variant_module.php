<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 04 — product_variants টেবিলকে ভ্যারিয়েন্ট মডিউলের স্পেসিফিকেশনে নিয়ে আসা।
     * পুরনো weight/discount_price/minimum_order/maximum_order বাদ,
     * quantity/compare_at_price/stock_status/is_default/sort_order/soft-delete যোগ।
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['weight', 'discount_price', 'minimum_order', 'maximum_order']);
        });

        // ফাঁকা SKU থাকলে অনন্য মান দিয়ে পূরণ — NOT NULL + UNIQUE constraint-এর আগে
        $hasEmptySku = DB::table('product_variants')->whereNull('sku')->exists();

        if ($hasEmptySku) {
            DB::table('product_variants')->whereNull('sku')->orderBy('id')->chunkById(100, function ($variants): void {
                foreach ($variants as $variant) {
                    DB::table('product_variants')->where('id', $variant->id)->update([
                        'sku' => 'VAR-'.str_pad((string) $variant->id, 6, '0', STR_PAD_LEFT),
                    ]);
                }
            });
        }

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->string('stock_status', 30)->default('in_stock');
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->softDeletes();

            $table->string('sku')->change();
            $table->unique('sku');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->string('sku')->nullable()->change();
            $table->dropColumn([
                'quantity',
                'compare_at_price',
                'stock_status',
                'is_default',
                'sort_order',
                'deleted_at',
            ]);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->integer('minimum_order')->default(1);
            $table->integer('maximum_order')->default(0);
        });
    }
};
