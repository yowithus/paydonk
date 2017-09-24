<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code', 'discount_percentage', 'max_discount', 'min_usage', 'started_at', 'ended_at', 'status'
    ];

    /**
     * The primary key associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'code';
}
