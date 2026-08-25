<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Product মডিউলের জন্য products টেবিলে নতুন কলাম যোগ
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // "আগের মূল্য" — ক্রস-আউট দাম দেখানোর জন্য
            $table->decimal('compare_at_price', 12, 2)->nullable()->after('discount_price');

            // মৌসুমি পণ্য ফ্ল্যাগ
            $table->boolean('is_seasonal')->default(false)->after('is_new_arrival');

            // স্টক স্ট্যাটাস: in_stock / out_of_stock
            $table->string('stock_status', 20)->default('in_stock')->after('is_active');

            // প্রদর্শনের ক্রম
            $table->integer('sort_order')->default(0)->after('stock_status');

            // পারফরম্যান্সের জন্য ইনডেক্স
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('is_bestseller');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['is_bestseller']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['sort_order', 'stock_status', 'is_seasonal', 'compare_at_price']);
        });
    }
};
