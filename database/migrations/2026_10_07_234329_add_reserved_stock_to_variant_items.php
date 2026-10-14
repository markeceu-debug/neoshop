<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variant_items', function (Blueprint $table) {
            $table->integer('reserved_stock')->default(0)->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('variant_items', function (Blueprint $table) {
            $table->dropColumn('reserved_stock');
        });
    }
};