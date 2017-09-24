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
        $image_name = $this->image_name;
        $category   = $this->category;

        if ($category == 'Pulsa') {
            $folder_name = 'pulsa';
        } else if ($category == 'PDAM') {
            $folder_name = 'pdam';
        } else if ($category == 'TV Kabel') {
            $folder_name = 'tv';
        } else if ($category == 'Angsuran Kredit') {
            $folder_name = 'finance';
        } else {
            return null;
        }

        $file_path =  "/images/products/$folder_name/$image_name";
        if (file_exists(public_path() . $file_path)) {
            return $file_path;
        }
    }
}
