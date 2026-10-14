<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'commission', 'net_amount',
        'method', 'bank_data',
        'status', 'reviewed_by', 'reviewed_at', 'paid_at',
        'rejection_reason', 'payment_reference',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'commission'  => 'decimal:2',
        'net_amount'  => 'decimal:2',
        'bank_data'   => 'array',
        'reviewed_at' => 'datetime',
        'paid_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            'paid'     => 'Pago',
            default    => '—',
        };
    }
}