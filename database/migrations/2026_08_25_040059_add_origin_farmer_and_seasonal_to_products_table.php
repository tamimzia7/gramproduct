<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('origin')->nullable()->after('product_type');
            $table->string('farmer_name')->nullable()->after('origin');
            $table->string('seasonal_info')->nullable()->after('farmer_name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['origin', 'farmer_name', 'seasonal_info']);
        });
    }
};
