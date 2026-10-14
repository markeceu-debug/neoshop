<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se a coluna stock existir, renomeia para deixar claro que só é usada quando não há variante
        if (Schema::hasColumn('products', 'stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('stock')->default(0)->change();
            });
        }

        // Marca se o produto tem variantes (cache)
        if (! Schema::hasColumn('products', 'has_variants')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('has_variants')->default(false)->after('stock');
                $table->integer('total_stock')->default(0)->after('has_variants'); // cache do total
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_variants', 'total_stock']);
        });
    }
};