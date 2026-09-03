<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_bn')->nullable()->after('name');
            $table->decimal('weight', 8, 2)->nullable()->after('unit');
            $table->string('brand')->nullable()->after('weight');
            $table->string('tags')->nullable()->after('brand');
            $table->integer('low_stock_threshold')->default(5)->after('tags');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_bn', 'weight', 'brand', 'tags', 'low_stock_threshold']);
        });
    }
};
