<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['status'];

    /**
     * The primary key associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that owns the order.
     */
    public function bank_transfer()
    {
        return $this->hasOne(TopUpBankTransfer::class, 'topup_order_id');
    }

    /**
     * Get the image for the product.
     */
    public function image()
    {
        $file_path =  '/images/products/' . $this->image_name;
        if (file_exists(public_path() . $file_path)) {
            return $file_path;
        }
    }
}
