<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BalanceDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'order_id', 'amount', 'previous_amount', 'current_amount', 'type'
    ];

    /**
     * Get the user that owns the deposit detail.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order that owns the deposit detail.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
