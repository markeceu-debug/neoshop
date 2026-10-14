<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'stockable_type',
        'stockable_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reason',
        'user_id',
        'reference',
    ];

    public function stockable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}