<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // ---- Vendedor ----
            $table->foreignId('seller_id')->nullable()->after('user_id')
                  ->constrained('users')->nullOnDelete();

            // ---- Comissão e valores do vendedor ----
            $table->decimal('commission_rate', 5, 2)->default(10.00)->after('total');
            $table->decimal('commission_amount', 12, 2)->default(0)->after('commission_rate');
            $table->decimal('seller_net_amount', 12, 2)->default(0)->after('commission_amount');

            // ---- Timeline de liberação ----
            $table->timestamp('approved_at')->nullable()->after('paid_at');
            $table->timestamp('auto_release_at')->nullable()->after('approved_at');
            $table->timestamp('released_at')->nullable()->after('auto_release_at');

            $table->index('seller_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropColumn([
                'seller_id',
                'commission_rate',
                'commission_amount',
                'seller_net_amount',
                'approved_at',
                'auto_release_at',
                'released_at',
            ]);
        });
    }
};