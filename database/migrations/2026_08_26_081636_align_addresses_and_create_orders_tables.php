<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 08 — চেকআউট:
     * - addresses: বাংলাদেশ-ভিত্তিক ঠিকানা কাঠামো (বিভাগ/জেলা/উপজেলা/এলাকা + delivery_note)
     * - orders / order_items: Phase 09 Order মডিউলের জন্য ন্যূনতম সীমানা
     *   (status=pending, payment_status=unpaid — payment এই ফেজে নয়)
     */
    public function up(): void
    {
        // ---------- addresses ----------
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['type', 'address_line_2', 'city', 'state', 'country']);
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->renameColumn('address_line_1', 'address_line');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->string('division', 60)->after('phone');
            $table->string('district', 60)->after('division');
            $table->string('upazila', 80)->after('district');
            $table->string('area')->after('upazila');
            $table->text('delivery_note')->nullable()->after('postal_code');

            $table->index(['user_id', 'is_default']);
        });

        // ---------- orders ----------
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();

            // ঠিকানা snapshot — Address পরে বদলালেও অর্ডার অপরিবর্তিত থাকে
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->string('receiver_name', 120);
            $table->string('receiver_phone', 20);
            $table->string('division', 60);
            $table->string('district', 60);
            $table->string('upazila', 80);
            $table->string('area');
            $table->text('address_line');
            $table->string('postal_code', 10)->nullable();
            $table->text('delivery_note')->nullable();

            $table->string('delivery_method', 30);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('grand_total', 12, 2);
            $table->string('currency', 3)->default('BDT');

            $table->string('payment_method', 30)->default('cod');
            $table->string('status', 30)->default('pending');
            $table->string('payment_status', 30)->default('unpaid');

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('inventory_transaction_id')->nullable();

            // snapshot — পরে variant/price বদলালেও অর্ডার ইতিহাস অক্ষত
            $table->string('product_name');
            $table->string('variant_name');
            $table->string('variant_sku', 50);
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);

            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_default']);
            $table->dropColumn(['division', 'district', 'upazila', 'area', 'delivery_note']);
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->renameColumn('address_line', 'address_line_1');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->string('type')->default('shipping');
            $table->text('address_line_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('country')->default('Bangladesh');
        });
    }
};
