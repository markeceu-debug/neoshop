<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('province', 100)->nullable()->after('phone');
            $table->string('municipality', 100)->nullable()->after('province');
            $table->string('neighborhood', 100)->nullable()->after('municipality');
            $table->string('address_reference', 200)->nullable()->after('neighborhood');

            $table->index('municipality');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['province', 'municipality', 'neighborhood', 'address_reference']);
        });
    }
};