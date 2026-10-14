<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
protected $fillable = [
    'name', 'email', 'telefone', 'avatar', 'role', 'password',
    'province', 'municipality', 'neighborhood', 'address_reference',
    'custom_shipping_enabled',
    'custom_shipping_fee',
    'custom_free_above',
    'custom_shipping_hours',
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',



    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'custom_shipping_enabled' => 'boolean',
    'custom_shipping_fee'     => 'decimal:2',
    'custom_free_above'       => 'decimal:2',
    'custom_shipping_hours'   => 'integer',
        ];
    }
    public function wallet()
{
    return $this->hasOne(Wallet::class);
}

public function withdrawals()
{
    return $this->hasMany(Withdrawal::class);
}

public function sales()
{
    return $this->hasMany(Order::class, 'seller_id');
}
public function carts()
{
    return $this->hasMany(Cart::class);
}

public function activeCart()
{
    return $this->hasOne(Cart::class)->where('status', 'open')->latestOfMany();
}
    // ---- Helpers de role ----
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function getAvatarUrlAttribute(): string
{
    return $this->avatar
        ? asset('storage/' . $this->avatar)
        : asset('assets/img/avatar-1.png');
}
}
