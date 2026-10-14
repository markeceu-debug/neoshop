<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('customer_province')->nullable()->after('coupon_code');
            $table->string('customer_municipality')->nullable()->after('customer_province');
            $table->string('shipping_method')->nullable()->after('customer_municipality');
            $table->integer('shipping_hours')->nullable()->after('shipping_method');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn([
                'customer_province',
                'customer_municipality',
                'shipping_method',
                'shipping_hours',
            ]);
        });
    }
};