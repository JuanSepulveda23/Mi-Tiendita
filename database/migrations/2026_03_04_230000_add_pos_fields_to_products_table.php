<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_frequent')->default(false)->after('stock');
            $table->integer('min_stock')->default(5)->after('is_frequent');
            $table->string('category')->nullable()->after('min_stock');
            $table->string('supplier')->nullable()->after('category');
            $table->integer('sort_order')->default(0)->after('supplier');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_frequent', 'min_stock', 'category', 'supplier', 'sort_order']);
        });
    }
};
