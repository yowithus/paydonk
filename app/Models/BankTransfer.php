<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankTransfer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 'topup_order_id', 'recipient_bank_id', 'sender_bank_name', 'sender_account_name', 'sender_account_number'
    ];

    /**
     * Get the user that owns the order.
     */
    public function recipient_bank()
    {
        return $this->belongsTo(RecipientBank::class);
    }
}
