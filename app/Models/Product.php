<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function units()
    {
        return $this->hasMany(ProductUnit::class, 'product_id');
    }

    public function requests()
    {
        return $this->hasMany(Request::class, 'product_id');
    }
}
