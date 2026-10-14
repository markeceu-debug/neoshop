<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = ['product_id', 'name', 'type', 'required', 'position'];
    protected $casts    = ['required' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(VariantItem::class)->orderBy('position');
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'padrao' ? 'Padrão' : 'Não Padrão';
    }
}