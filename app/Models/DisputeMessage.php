<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeMessage extends Model
{
    protected $fillable = [
        'dispute_id', 'sender_id', 'sender_role',
        'message', 'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function getSenderNameAttribute(): string
    {
        if ($this->sender_role === 'system') {
            return 'NeoShop';
        }
        return $this->sender?->name ?? 'Sistema';
    }

    public function getSenderBadgeAttribute(): array
    {
        return match ($this->sender_role) {
            'buyer'  => ['admin-badge-info',    'Cliente'],
            'admin'  => ['admin-badge-purple',  'Admin'],
            'system' => ['admin-badge-muted',   'Sistema'],
            default  => ['admin-badge-muted',   '—'],
        };
    }
}