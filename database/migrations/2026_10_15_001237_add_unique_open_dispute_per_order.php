<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Limpa possíveis duplicatas antes de criar a constraint
        //    (mantém só a disputa mais antiga ABERTA por pedido)
        $duplicatas = DB::table('disputes')
            ->select('order_id', DB::raw('MIN(id) as keep_id'))
            ->whereIn('status', ['open', 'under_review'])
            ->groupBy('order_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicatas as $dup) {
            DB::table('disputes')
                ->where('order_id', $dup->order_id)
                ->whereIn('status', ['open', 'under_review'])
                ->where('id', '!=', $dup->keep_id)
                ->update([
                    'status'           => 'resolved_seller',
                    'resolution_notes' => 'Encerrada automaticamente por duplicidade (migration).',
                    'resolved_at'      => now(),
                ]);
        }

        // 2. Cria coluna gerada que só tem valor quando a disputa está ativa
        //    NULL em todas as outras linhas — permitindo múltiplas resolvidas
        //    no mesmo pedido.
        DB::statement("
            ALTER TABLE disputes
            ADD COLUMN active_order_id BIGINT UNSIGNED
                GENERATED ALWAYS AS (
                    CASE
                        WHEN status IN ('open', 'under_review') THEN order_id
                        ELSE NULL
                    END
                ) STORED
        ");

        // 3. Índice único sobre a coluna gerada
        DB::statement('
            CREATE UNIQUE INDEX disputes_active_order_unique
            ON disputes (active_order_id)
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX disputes_active_order_unique ON disputes');

        Schema::table('disputes', function (Blueprint $table) {
            $table->dropColumn('active_order_id');
        });
    }
};