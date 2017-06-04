<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone_number', 'password', 'deposit', 'status', 'device_id', 'dji_merchant_id', 'dji_password', 'dji_pin'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the photo for the user.
     */
    public function photo()
    {
        $filename = md5('user-' . $this->id) . '.jpg';
        if (file_exists(public_path() . '/images/users/'. $filename)) {
            return $filename;
        } else {
            return 'default.png';
        }     
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    /**
     * Get the top up orders for the user.
     */
    public function topUpOrders()
    {
        return $this->hasMany(TopUpOrder::class);
    }

}
