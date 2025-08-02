<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = ['area_name', 'post_code',];
    public function shippingCharge()
    {
        return $this->hasMany(ShippingCharge::class);
    }
}
