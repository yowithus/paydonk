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
        'first_name', 'last_name', 'email', 'phone_number', 'password', 'deposit', 'status', 'fcm_token_android', 'fcm_token_ios'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
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
    public function top_up_orders()
    {
        return $this->hasMany(TopUpOrder::class);
    }

    /**
     * Get the credit card token for the user.
     */
    public function credit_card_token()
    {
        return $this->hasOne(CreditCardToken::class);
    }

    /**
     * Get the balance details for the user.
     */
    public function balance_details()
    {
        return $this->hasMany(BalanceDetail::class);
    }
}
