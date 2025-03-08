<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    public function units()
    {
        return $this->hasMany(ProductUnit::class, 'product_id');
    }

    public function requests()
    {
        return $this->hasMany(Request::class, 'product_id');
    }
}
