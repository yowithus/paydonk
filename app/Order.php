<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'product_code', 'reference_id', 'customer_number', 'product_price', 'admin_fee', 'order_amount', 'order_status', 'discount_amount', 'payment_amount', 'payment_status', 'payment_method', 'promo_code'
    ];

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that owns the order.
     */
    public function bank_transfer()
    {
        return $this->hasOne(BankTransfer::class);
    }
}
