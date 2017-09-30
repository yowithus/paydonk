<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditCardToken extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'token_id', 'credit_card_number', 'credit_card_type'
    ];
}
