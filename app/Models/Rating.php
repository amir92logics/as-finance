<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rating extends Model
{
    use HasFactory;

    public function comment() :HasOne
    {
        return $this->hasOne(RatingComment::class,'rating_id', 'id');
    }

    public function user() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,'user_id', 'id');
    }

    public function product() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class,'product_id', 'id');
    }
}
