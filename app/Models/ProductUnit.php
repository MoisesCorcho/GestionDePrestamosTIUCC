<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function requestUnits()
    {
        return $this->hasMany(RequestItem::class, 'unit_id');
    }
}
