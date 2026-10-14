<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    protected $fillable = [
        'order_id', 'uploaded_by',
        'file_path', 'file_type', 'file_size', 'notes',
        'status', 'reviewed_by', 'reviewed_at', 'rejection_reason',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isApproved(): bool   { return $this->status === 'approved'; }
    public function isRejected(): bool   { return $this->status === 'rejected'; }
}