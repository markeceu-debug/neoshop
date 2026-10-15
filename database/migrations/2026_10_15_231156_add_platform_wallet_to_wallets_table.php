<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
             $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->boolean('is_platform')->default(false)->after('user_id');

            $table->index('is_platform');
        });

        // Cria a wallet da plataforma (user_id = null)
        if (! DB::table('wallets')->where('is_platform', true)->exists()) {
            DB::table('wallets')->insert([
                'user_id'          => null,
                'is_platform'      => true,
                'balance'          => 0,
                'pending_balance'  => 0,
                'withdrawal_hold'  => 0,
                'total_earned'     => 0,
                'total_withdrawn'  => 0,
                'blocked'          => 0,
                'block_reason'     => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('wallets')->where('is_platform', true)->delete();

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropIndex(['is_platform']);
            $table->dropColumn('is_platform');
        });
    }
};