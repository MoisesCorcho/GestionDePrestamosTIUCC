<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProductUnit extends Model
{
    use SoftDeletes;

    public function fechaAsignacion(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $this->attributes['fecha_asignacion'] = $value ?? now(), // Asigna la fecha actual si $value es nulo
        );
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function requestProductUnits()
    {
        return $this->hasMany(RequestProductUnit::class, 'product_unit_id');
    }
}
