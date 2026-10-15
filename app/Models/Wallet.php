<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
         'is_platform',
        'pending_balance',
        'withdrawal_hold',
        'total_earned',
        'total_withdrawn',
        'blocked',
        'block_reason',
    ];

    protected $casts = [
        'balance'         => 'decimal:2',
        'is_platform' => 'boolean',   
        'pending_balance' => 'decimal:2',
        'withdrawal_hold' => 'decimal:2',
        'total_earned'    => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'blocked'         => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    /* ============ Helpers ============ */

    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId]);
    }

    public function canWithdraw(float $amount): bool
    {
        return ! $this->blocked && $this->balance >= $amount;
    }

    public function getAvailableForWithdrawalAttribute(): float
    {
        return (float) $this->balance;
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->balance + (float) $this->pending_balance;
    }

    public function refund(float $amount, ?int $orderId, string $description): void
    {
        $before = (float) $this->balance;
        $after  = $before + $amount;

        $this->update(['balance' => $after]);

        WalletTransaction::create([
            'wallet_id'      => $this->id,
            'order_id'       => $orderId,
            'type'           => 'refund',
            'amount'         => $amount,
            'balance_before' => $before,
            'balance_after'  => $after,
            'description'    => $description,
        ]);
    }

    /**
 * Wallet da plataforma (admin). Não tem user_id.
 */
public static function forPlatform(): self
{
    return static::firstOrCreate(
        ['is_platform' => true],
        [
            'user_id'         => null,
            'balance'         => 0,
            'pending_balance' => 0,
            'withdrawal_hold' => 0,
            'total_earned'    => 0,
            'total_withdrawn' => 0,
            'blocked'         => 0,
        ]
    );
}

/**
 * Credita a comissão da plataforma.
 */
public function creditCommission(float $amount, ?int $orderId = null, ?string $description = null): void
{
    if ($amount <= 0) return;

    $before = (float) $this->balance;

    $this->update([
        'balance'      => $before + $amount,
        'total_earned' => (float) $this->total_earned + $amount,
    ]);

    WalletTransaction::create([
        'wallet_id'      => $this->id,
        'order_id'       => $orderId,
        'type'           => 'commission',
        'amount'         => $amount,
        'balance_before' => $before,
        'balance_after'  => $before + $amount,
        'description'    => $description ?? 'Comissão da plataforma',
        'created_by'     => null,
    ]);
}

/**
 * Estorna (debita) a comissão da plataforma.
 * Usado quando há reembolso em disputa.
 */
public function debitCommission(float $amount, ?int $orderId = null, ?string $description = null): void
{
    if ($amount <= 0) return;

    $before = (float) $this->balance;
    $after  = max(0, $before - $amount);

    $this->update([
        'balance'      => $after,
        'total_earned' => max(0, (float) $this->total_earned - $amount),
    ]);

    WalletTransaction::create([
        'wallet_id'      => $this->id,
        'order_id'       => $orderId,
        'type'           => 'commission_refund',
        'amount'         => -$amount,
        'balance_before' => $before,
        'balance_after'  => $after,
        'description'    => $description ?? 'Estorno de comissão',
        'created_by'     => null,
    ]);
}
}
