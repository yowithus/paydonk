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
        'user_id', 'product_code', 'reference_id', 'customer_number', 'product_price', 'admin_fee', 'order_amount', 'status', 'discount_amount', 'payment_amount', 'unique_code', 'payment_method', 'customer_name', 'billing_period', 'promo_id', 'temp_promo_code', 'cancellation_reason'
    ];

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bank transfer of the order.
     */
    public function bank_transfer()
    {
        return $this->hasOne(BankTransfer::class);
    }

    /**
     * Get the product of the order.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the promo of the order.
     */
    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    /**
     * Get the refund of the order.
     */
    public function refund()
    {
        return $this->hasOne(Refund::class);
    }
}
