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
        'order_id', 'recipient_bank_id', 'sender_bank_id', 'sender_account_name', 'sender_account_number'
    ];

    /**
     * Get the recipient bank of the bank transfer.
     */
    public function recipient_bank()
    {
        return $this->belongsTo(RecipientBank::class);
    }

    /**
     * Get the sender bank of the bank transfer.
     */
    public function sender_bank()
    {
        return $this->belongsTo(SenderBank::class);
    }
}
