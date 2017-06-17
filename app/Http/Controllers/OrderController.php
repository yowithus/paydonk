<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use App\TopUpOrder;
use App\TopUpBankTransfer;
use App\DepositDetail;
use App\Order;
use App\BankTransfer;
use DB;

class OrderController extends Controller
{
    public function __construct()
    {
    	$this->middleware('jwt.auth', ['except' => ['getRecipientBanks', 'getSenderBanks', 'getTopUpNominals']]);
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

    public function createTopUpOrder(Request $request) 
    {
        $validator = validator()->make($request->all(), [
            'topup_nominal'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

    	$user = JWTAuth::parseToken()->authenticate();

        $user_id = $user->id;

    	$topup_order = TopUpOrder::create([
            'user_id'           => $user_id,
            'reference_id'      => 'TLT' . $this->generateReferenceId(5),
            'order_amount'      => $request->topup_nominal,
            'order_status' 	    => 0,
            'payment_amount'    => $request->topup_nominal,
            'payment_status'    => 0,
            'payment_method'    => 'Bank Transfer'
        ]);

        return response()->json([
            'status'      => 1,
            'message'     => 'Create top up order successful',
            'topup_order' => $topup_order,
        ]);
    }

    public function confirmTopUpOrder(Request $request) 
    {
    	$validator = validator()->make($request->all(), [
            'topup_order_id'        => 'required',
            'recipient_bank_id'     => 'required',
            'sender_account_name' 	=> 'required',
            'sender_account_number' => 'required',
            'sender_bank_name' 		=> 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

    	$user = JWTAuth::parseToken()->authenticate();

        TopUpOrder::where('id', $request->topup_order_id)
            ->update(['payment_status' => 1]);

        TopUpBankTransfer::create([
            'topup_order_id'        => $request->topup_order_id,
            'recipient_bank_id'     => $request->recipient_bank_id,
            'sender_account_name'   => $request->sender_account_name,
            'sender_account_number' => $request->sender_account_number,
            'sender_bank_name'      => $request->sender_bank_name,
        ]);

    	return response()->json([
            'status'    => 1,
            'message'   => 'Confirm top up order successful'
        ]);
    }


    public function getNominals($product_code) 
    {
        $topup_nominals = DB::table('topup_nominals')
            ->where('status', 1)
            ->get();

        return response()->json(compact('topup_nominals'));
    }


    public function getInvoice($product_code) 
    {

    }


    public function createOrder(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'product_code'  => 'required',
            'order_amount'  => 'required',
            'customer_number' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();

        $user_id = $user->id;

        $order = Order::create([
            'user_id'           => $user_id,
            'product_code'      => $request->product_code,
            'reference_id'      => 'TLT' . $this->generateReferenceId(5),
            'order_amount'      => $request->order_amount,
            'order_status'      => 0,
            'payment_amount'    => $request->order_amount,
            'payment_status'    => 0,
            'payment_method'    => 'Bank Transfer'
        ]);

        return response()->json([
            'status'    => 1,
            'message'   => 'Create order successful',
            'order'     => $order,
        ]);
    }

    public function confirmOrder(Request $request) 
    {

    }


    private function generateReferenceId($length) 
    {
        $key = '';
        $keys = array_merge(range(0, 9));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }
}
