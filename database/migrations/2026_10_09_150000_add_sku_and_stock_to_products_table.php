<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', static function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('name');
            $table->unsignedInteger('stock')->default(0)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('products', static function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn(['sku', 'stock']);
        });
    }
};
