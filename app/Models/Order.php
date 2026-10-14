<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
   protected $fillable = [
    'number',
    'user_id',
    'seller_id',
    'status',
    'subtotal',
    'discount',
    'shipping',
    'total',
    'commission_rate',
    'commission_amount',
    'seller_net_amount',
    'customer_name',
    'customer_email',
    'customer_phone',
    'shipping_address',
    'billing_address',
    'payment_method',
    'payment_status',
    'shipping_method',
    'tracking_code',
    'paid_at',
    'approved_at',
    'auto_release_at',
    'released_at',
    'shipped_at',
    'delivered_at',
    'canceled_at',
    'notes',
    'courier_name',
    'courier_phone',
    'shipping_note',
     'refunded_at',
    'refund_amount',
];

protected $casts = [
    'subtotal'          => 'decimal:2',
    'discount'          => 'decimal:2',
    'shipping'          => 'decimal:2',
    'total'             => 'decimal:2',
    'commission_rate'   => 'decimal:2',
    'commission_amount' => 'decimal:2',
    'seller_net_amount' => 'decimal:2',
    'shipping_address'  => 'array',
    'billing_address'   => 'array',
    'paid_at'           => 'datetime',
    'approved_at'       => 'datetime',
    'auto_release_at'   => 'datetime',
    'released_at'       => 'datetime',
    'shipped_at'        => 'datetime',
    'delivered_at'      => 'datetime',
    'canceled_at'       => 'datetime',
    'refunded_at'   => 'datetime',
    'refund_amount' => 'decimal:2',
];

public function trackingEvents(): HasMany
{
    return $this->hasMany(OrderTrackingEvent::class)->latest();
}

public function latestTrackingEvent(): ?OrderTrackingEvent
{
    return $this->trackingEvents()->first();
}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
public function seller(): BelongsTo
{
    return $this->belongsTo(User::class, 'seller_id');
}

public function paymentProofs(): HasMany
{
    return $this->hasMany(PaymentProof::class);
}

public function approvedProof(): ?PaymentProof
{
    return $this->paymentProofs()->where('status', 'approved')->latest()->first();
}

public function disputes(): HasMany
{
    return $this->hasMany(Dispute::class);
}

public function openDispute(): ?Dispute
{
    return $this->disputes()->whereIn('status', ['open', 'under_review'])->first();
}

    /* ============ Helpers ============ */

    public static function generateNumber(): string
    {
        $year = date('Y');
        $seq  = str_pad((string) (static::whereYear('created_at', $year)->count() + 1), 5, '0', STR_PAD_LEFT);
        $rand = strtoupper(Str::random(3));

        return "NEO-{$year}-{$seq}{$rand}";
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Aguardando Pagamento',
            'paid'      => 'Pago',
            'shipped'   => 'Enviado',
            'delivered' => 'Entregue',
            'canceled'  => 'Cancelado',
            default     => '—',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        $map = [
            'pending'   => 'admin-badge-warning',
            'paid'      => 'admin-badge-success',
            'shipped'   => 'admin-badge-info',
            'delivered' => 'admin-badge-success',
            'canceled'  => 'admin-badge-danger',
        ];
        return $map[$this->status] ?? 'admin-badge-muted';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function latestDispute()
{
    return $this->hasOne(Dispute::class)->latestOfMany();
}

public function isDisputed(): bool
{
    return $this->status === 'disputed';
}

public function isDisputable(): bool
{
    // Só pode disputar se foi entregue E está dentro do prazo
    if ($this->status !== 'delivered') return false;
    if (! $this->delivered_at) return false;

    $guaranteeDays = (int) \App\Models\Setting::get('guarantee_days', 7);

    return $this->delivered_at->copy()->addDays($guaranteeDays)->isFuture();
}

public function isCancelable(): bool
{
    // Cancelamento só antes do envio
    return in_array($this->status, ['awaiting_payment', 'payment_under_review', 'paid']);
}
}