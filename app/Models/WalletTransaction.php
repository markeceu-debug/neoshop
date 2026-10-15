<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id', 'order_id', 'withdrawal_id',
        'type', 'amount', 'balance_before', 'balance_after',
        'description', 'created_by','commission','commission_refund',    // ← adicionar
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',

    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'sale_pending'    => 'Venda (pendente)',
            'sale_released'   => 'Venda liberada',
            'commission'      => 'Comissão',
            'withdrawal'      => 'Saque solicitado',
            'withdrawal_paid' => 'Saque pago',
            'refund'          => 'Reembolso',
            'adjustment'      => 'Ajuste',
            default           => $this->type,
        };
    }
}