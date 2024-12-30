<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function requestProductUnits()
    {
        return $this->hasMany(RequestProductUnit::class, 'product_unit_id');
    }
}
