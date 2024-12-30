<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestProductUnit extends Model
{
    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }
}
