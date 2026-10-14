<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LowStockAlert extends Model
{
    protected $fillable = ['stockable_type', 'stockable_id', 'last_stock', 'notified_at'];
    protected $casts    = ['notified_at' => 'datetime'];

    public function stockable()
    {
        return $this->morphTo();
    }
}