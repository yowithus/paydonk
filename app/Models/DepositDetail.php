<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepositDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'topup_order_id', 'order_id', 'amount', 'previous_amount', 'current_amount', 'type'
    ];

    /**
     * Get the user that owns the deposit detail.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the top up order that owns the deposit detail.
     */
    public function topup_order()
    {
        return $this->belongsTo(TopUpOrder::class);
    }

    /**
     * Get the order that owns the deposit detail.
     */
    public function order()
    {
        return $this->belongsTo(order::class);
    }
}
