<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispute extends Model
{
    protected $fillable = [
        'order_id', 'opened_by',
        'reason', 'description', 'attachments',
        'status', 'resolution_notes', 'refund_amount',
        'resolved_by', 'resolved_at',
    ];

    protected $casts = [
        'attachments'   => 'array',
        'refund_amount' => 'decimal:2',
        'resolved_at'   => 'datetime',
    ];

    /* ============ Relacionamentos ============ */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class)->orderBy('created_at');
    }

    /* ============ Helpers ============ */

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'under_review']);
    }

    public function isResolved(): bool
    {
        return ! $this->isOpen();
    }

    public function getReasonLabelAttribute(): string
{
    return match ($this->reason) {
        'not_received'     => 'Produto não recebido',
        'wrong_product'    => 'Produto errado',
        'defective'        => 'Produto com defeito',
        'damaged'          => 'Produto danificado',
        'not_as_described' => 'Diferente do anunciado',
        'other'            => 'Outro motivo',
        default            => $this->reason ?? '—',
    };
}

  public function getStatusLabelAttribute(): string
{
    return match ($this->status) {
        'open'             => 'Aberta',
        'under_review'     => 'Em Análise',
        'resolved_buyer'   => 'Resolvido (Cliente)',
        'resolved_seller'  => 'Resolvido (Vendedor)',
        'resolved_partial' => 'Resolvido (Parcial)',
        'closed'           => 'Fechada',
        default            => $this->status ?? '—',
    };
}
  public function getStatusBadgeAttribute(): array
{
    return match ($this->status) {
        'open'             => ['admin-badge-warning', 'Aberta'],
        'under_review'     => ['admin-badge-info',    'Em Análise'],
        'resolved_buyer'   => ['admin-badge-success', 'Cliente Reembolsado'],
        'resolved_seller'  => ['admin-badge-muted',   'Vendedor Liberado'],
        'resolved_partial' => ['admin-badge-info',    'Reembolso Parcial'],
        'closed'           => ['admin-badge-muted',   'Fechada'],
        default            => ['admin-badge-muted',   $this->status ?? '—'],
    };
}
}