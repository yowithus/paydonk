<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 'amount', 'status'
    ];
}
