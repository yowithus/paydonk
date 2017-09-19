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
    	$this->middleware('jwt.auth', ['except' => [
            'getRecipientBanks', 
            'getSenderBanks', 
            'getTopUpNominals', 
            'getPrepaidPLNProducts', 
            'getPDAMProducts',
            'getTVProducts',
            'getFinanceProducts', 
            'getPostpaidPulsaProduct',
        ]]);
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

    public function getTopUpNominals() 
    {
    	$topup_nominals = DB::table('topup_nominals')
    		->where('status', 1)
    		->get();

    	return response()->json([
            'status'      => 1,
            'message'     => 'Get top up nominals successful',
            'topup_nominals' => $topup_nominals,
        ]);
    }

    public function getPrepaidPLNProducts() 
    {
        $pln_products = Product::selectRaw('variant_name as name, price, code')
            ->where('category', 'PLN')
            ->where('type', 'Prepaid')
            ->where('status', 1)
            ->get();

        return response()->json([
            'status'    => 1,
            'message'   => 'Get token listrik products successful',
            'pln_products'  => $pln_products,
        ]);
    }

    public function getPDAMProducts() 
    {
        $pdam_products = Product::selectRaw('name, code, image_name')
            ->where('category', 'PDAM')
            ->where('status', 1)
            ->get();

        return response()->json([
            'status'    => 1,
            'message'   => 'Get pdam products successful',
            'pdam_products'  => $pdam_products,
        ]);
    }

    public function getTVProducts() 
    {
        $tv_products = Product::selectRaw('name, variant_name, code, type, image_name')
            ->where('category', 'TV Kabel')
            ->where('status', 1)
            ->get();

        return response()->json([
            'status'    => 1,
            'message'   => 'Get tv kabel products successful',
            'tv_products'  => $tv_products,
        ]);
    }

    public function getFinanceProducts() 
    {
        $finance_products = Product::selectRaw('name, code, image_name')
            ->where('category', 'Angsuran Kredit')
            ->where('status', 1)
            ->get();

        return response()->json([
            'status'    => 1,
            'message'   => 'Get angsuran kredit products successful',
            'finance_products'  => $finance_products,
        ]);
    }

    public function getPostpaidPulsaProduct(Request $request) 
    {
        $validator = validator()->make($request->all(), [
            'customer_number'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $prefixes = [
            // Indosat
            '+62814' => 'Indosat',
            '+62815' => 'Indosat',
            '+62816' => 'Indosat',
            '+62855' => 'Indosat',
            '+62856' => 'Indosat',
            '+62857' => 'Indosat',
            '+62858' => 'Indosat',
            '+62817' => 'Indosat',
            '+62817' => 'Indosat',
            '+62817' => 'Indosat',

            // Telkomsel
            '+62811' => 'Telkomsel',
            '+62812' => 'Telkomsel',
            '+62813' => 'Telkomsel',
            '+62821' => 'Telkomsel',
            '+62822' => 'Telkomsel',
            '+62823' => 'Telkomsel',
            '+62851' => 'Telkomsel',
            '+62852' => 'Telkomsel',
            '+62853' => 'Telkomsel',

            // XL
            '+62817' => 'XL',
            '+62818' => 'XL',
            '+62819' => 'XL',
            '+62859' => 'XL',
            '+62877' => 'XL',
            '+62878' => 'XL',

            // Axis
            '+62831' => 'Axis',
            '+62832' => 'Axis',
            '+62833' => 'Axis',
            '+62838' => 'Axis',

            // Smartfren
            '+62881' => 'Smartfren',
            '+62882' => 'Smartfren',
            '+62883' => 'Smartfren',
            '+62884' => 'Smartfren',
            '+62885' => 'Smartfren',
            '+62886' => 'Smartfren',
            '+62887' => 'Smartfren',
            '+62888' => 'Smartfren',
            '+62889' => 'Smartfren',

            // 3
            '+62895' => '3',
            '+62896' => '3',
            '+62897' => '3',
            '+62898' => '3',
            '+62899' => '3',
        ];

        $customer_number = $request->customer_number;
        $prefix = substr($customer_number, 0, 6);

        $operator = $prefixes[$prefix];

        $pulsa_product = Product::selectRaw('name, code, image_name')
            ->where('name', $operator)
            ->where('category', 'Pulsa')
            ->where('type', 'Postpaid')
            ->where('status', 1)
            ->first();

        if ($pulsa_product) {
            return response()->json([
                'status'    => 1,
                'message'   => 'Get pulsa pascabayar product successful',
                'pulsa_product'  => $pulsa_product,
            ]);
        } else {
            return response()->json([
                'status'    => 0,
                'message'   => 'Get pulsa pascabayar product failed',
            ]);
        }
    }
}
