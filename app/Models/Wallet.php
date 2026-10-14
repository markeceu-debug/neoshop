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
        'pending_balance',
        'withdrawal_hold',
        'total_earned',
        'total_withdrawn',
        'blocked',
        'block_reason',
    ];

    protected $casts = [
        'balance'         => 'decimal:2',
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
}
