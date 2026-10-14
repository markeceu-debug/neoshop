<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'active',
        'position',
    ];

    protected $casts = [
        'active'   => 'boolean',
        'position' => 'integer',
    ];

    // Auto-slug ao criar
    protected static function booted(): void
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name) . '-' . uniqid();
            }
        });
    }

    // ---- Relacionamento ----
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    // ---- Acessores ----
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : 'https://picsum.photos/seed/cat' . $this->id . '/80/80';
    }

    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }
}