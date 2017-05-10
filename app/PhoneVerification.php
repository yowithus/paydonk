<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhoneVerification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'phone_number', 'verification_code'
    ];

    public function setUpdatedAt($value) {
	}
}
