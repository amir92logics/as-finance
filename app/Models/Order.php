<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public function area()
    {
        return $this->hasOne(Area::class, 'id', 'area_id');
    }

    public function fund()
    {
        return $this->hasOne(Deposit::class, 'depositable_id');
    }

    public function orderItem()
    {
        return $this->hasMany(OrderItems::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class, 'gateway_id', 'id');
    }

    public function depositable()
    {
        return $this->morphOne(Deposit::class, 'depositable');
    }

    public function transactional()
    {
        return $this->morphOne(Transaction::class, 'transactional');
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber()
    {
        return DB::transaction(function () {
            $lastOrder = self::lockForUpdate()->orderBy('id', 'desc')->first();
            if ($lastOrder && isset($lastOrder->order_number)) {
                $lastOrderNumber = (int)filter_var($lastOrder->order_number, FILTER_SANITIZE_NUMBER_INT);
                $newOrderNumber = $lastOrderNumber + 1;
            } else {
                $newOrderNumber = strRandomNum(6);
            }

            // Check again to ensure the new trx_id doesn't already exist (extra safety)
            while (self::where('order_number', '#'.$newOrderNumber)->exists()) {
                $newOrderNumber = (int)$newOrderNumber + 1;
            }
            return '#' . $newOrderNumber;
        });
    }


    public function orderStatus() : string
    {
        switch ($this->order_status) {

            case 0:
                return '<span class="badge text-bg-warning">' . trans('Pending') . '</span>';
            case 1:
                return '<span class="badge text-bg-info">' . trans('Order Placed') . '</span>';
            case 2:
                return '<span class="badge text-bg-success">' . trans('Delivered') . '</span>';
            case 3:
                return '<span class="badge text-bg-danger">' . trans('Canceled') . '</span>';
            default:
                return '<span class="badge text-bg-danger">' . trans('Unknown') . '</span>';
        }
    }

    public function getPaymentByAttribute()
    {
        $paymentBy = null;
        if ($this->gateway_id == 2000)
            $paymentBy = 'Cash on Delivery';
        elseif ($this->gateway_id == 2100)
            $paymentBy = 'Wallet';
        else
            $paymentBy = $this->gateway->name ?? 'N/A';

        return $paymentBy;
    }
}
