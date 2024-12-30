<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\RequestObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([RequestObserver::class])]
class Request extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function requestProductUnits()
    {
        return $this->hasMany(RequestProductUnit::class, 'request_id');
    }
}
