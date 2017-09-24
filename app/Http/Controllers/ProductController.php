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
            '0814' => 'Indosat',
            '0815' => 'Indosat',
            '0816' => 'Indosat',
            '0855' => 'Indosat',
            '0856' => 'Indosat',
            '0857' => 'Indosat',
            '0858' => 'Indosat',
            '0817' => 'Indosat',
            '0817' => 'Indosat',
            '0817' => 'Indosat',

            // Telkomsel Halo
            '0811' => 'Telkomsel Halo',

            // Telkomsel
            '0812' => 'Telkomsel',
            '0813' => 'Telkomsel',
            '0821' => 'Telkomsel',
            '0822' => 'Telkomsel',
            '0823' => 'Telkomsel',
            '0851' => 'Telkomsel',
            '0852' => 'Telkomsel',
            '0853' => 'Telkomsel',

            // XL
            '0817' => 'XL',
            '0818' => 'XL',
            '0819' => 'XL',
            '0859' => 'XL',
            '0877' => 'XL',
            '0878' => 'XL',

            // Axis
            '0831' => 'Axis',
            '0832' => 'Axis',
            '0833' => 'Axis',
            '0838' => 'Axis',

            // Smartfren
            '0881' => 'Smartfren',
            '0882' => 'Smartfren',
            '0883' => 'Smartfren',
            '0884' => 'Smartfren',
            '0885' => 'Smartfren',
            '0886' => 'Smartfren',
            '0887' => 'Smartfren',
            '0888' => 'Smartfren',
            '0889' => 'Smartfren',

            // 3
            '0895' => '3',
            '0896' => '3',
            '0897' => '3',
            '0898' => '3',
            '0899' => '3',
        ];

        $customer_number = $request->customer_number;
        $prefix = substr($customer_number, 0, 4);

        if (isset($prefixes[$prefix])) {
            $operator = $prefixes[$prefix];
        } else {
            return response()->json([
                'status'    => 0,
                'message'   => 'The number is currently not supported.'
            ]);
        }

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
