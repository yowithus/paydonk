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
            'message'     => 'Get recipient banks successful',
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
            'message'     => 'Get sender banks successful',
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

        $category = $request->category;

        $products = Product::selectRaw('name, variant_name, price, code, type, image_name')
            ->where('category', $category);

        if ($category == 'PLN') {
            $products->where('type', 'Prepaid');
        } else if (in_array($category, ['Pulsa', 'Paket Data'])) {
            $type   = $request->type;
            $customer_number = $request->customer_number;
            $prefix = substr($customer_number, 0, 4);

            if (isset(OPERATOR_PREFIXES[$prefix])) {
                $operator = OPERATOR_PREFIXES[$prefix];
            } else {
                return response()->json([
                    'status'    => 0,
                    'message'   => 'The number is currently not supported.'
                ]);
            }

            $products->where('name', $operator)
                ->where('type', $type);
        }

        $products = $products->where('status', 1)
            ->get();

        return response()->json([
            'status'    => 1,
            'message'   => 'Get products successful',
            'products'  => $products,
        ]);
    }
}
