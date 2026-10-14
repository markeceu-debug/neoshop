<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('custom_shipping_enabled')->default(false)->after('address_reference');
            $table->decimal('custom_shipping_fee', 12, 2)->nullable()->after('custom_shipping_enabled');
            $table->decimal('custom_free_above', 12, 2)->nullable()->after('custom_shipping_fee');
            $table->integer('custom_shipping_hours')->nullable()->after('custom_free_above');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'custom_shipping_enabled',
                'custom_shipping_fee',
                'custom_free_above',
                'custom_shipping_hours',
            ]);
        });
    }
};