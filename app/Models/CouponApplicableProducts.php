<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponApplicableProducts extends Model
{
    use HasFactory;

    protected $guarded  = ['id'];

    public function coupon():BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
