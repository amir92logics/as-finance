<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetails extends Model
{
    use HasFactory,Translatable;

    protected $guarded = ['id'];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
