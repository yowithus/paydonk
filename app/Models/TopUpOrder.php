<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TopUpOrder extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'reference_id', 'order_amount', 'order_status', 'payment_amount', 'payment_status', 'payment_method'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'topup_orders';

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
        return $this->hasOne(BankTransfer::class, 'topup_order_id');
    }
}
