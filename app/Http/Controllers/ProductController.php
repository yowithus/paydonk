<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Product;
use DB;

class ProductController extends Controller
{
    public function __construct()
    {
    }  

    public function getRecipientBanks()
    {
    	$recipient_banks = DB::table('recipient_banks')
    		->where('status', 1)
    		->get();

        return response()->json([
            'status'      => 1,
            'message'     => trans('messages.success', ['action' => trans('action.get_recipient_banks')]),
            'recipient_banks' => $recipient_banks,
        ]);
    }

    public function getSenderBanks()
    {
    	$sender_banks = DB::table('sender_banks')
    		->where('status', 1)
    		->get();

        return response()->json([
            'status'      => 1,
            'message'     => trans('messages.success', ['action' => trans('action.get_sender_banks')]),
            'sender_banks' => $sender_banks,
        ]);
    }

    public function getProducts(Request $request) 
    {
        $validator = validator()->make($request->all(), [
            'category' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $category   = $request->category;
        $type       = $request->type;
        $customer_number = $request->customer_number;
        
        $products = Product::selectRaw('id, name, if(variant_name is null, price, null) as price, if(variant_name is null, code, null) as code, category, type, image_name')
            ->where('category', $category);

        if ($category == 'PLN') {
            $products->where('type', 'Prepaid');
        } else if (in_array($category, ['Pulsa', 'Paket Data'])) {
            
            $prefix = substr($customer_number, 0, 4);

            if (isset(OPERATOR_PREFIXES[$prefix])) {
                $operator = OPERATOR_PREFIXES[$prefix];
            } else {
                return response()->json([
                    'status'    => 0,
                    'message'   => trans('messages.error_invalid_operator'),
                ]);
            }

            $products->where('name', $operator)
                ->where('type', $type);
        }

        $products = $products->where('status', 1)
            ->groupBy('name')
            ->get();

        foreach ($products as $product) {
            $product_variants = Product::selectRaw('variant_name as name, price, code')
                ->where('category', $category)
                ->where('name', $product->name)    
                ->where('type', $product->type)
                ->where('variant_name', '!=', null)
                ->get();

            $product->customer_number = $customer_number;
            $product->variants = (count($product_variants) > 0) ? $product_variants : null;
        }

        return response()->json([
            'status'    => 1,
            'message'   => trans('messages.success', ['action' => trans('action.get_products')]),
            'products'  => $products,
        ]);
    }
}
