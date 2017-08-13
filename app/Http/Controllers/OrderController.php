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
use App\Product;
use DB;

class OrderController extends Controller
{
    public function __construct()
    {
    	$this->middleware('jwt.auth', ['except' => ['getRecipientBanks', 'getSenderBanks', 'getTopUpNominals', 'getNominals', 'getPDAMProducts']]);
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

        $now_id = TopUpOrder::latest()->value('id') + 1;
        $reference_id = sprintf("1001%09d", $now_id);

    	$topup_order = TopUpOrder::create([
            'user_id'           => $user_id,
            'reference_id'      => $reference_id,
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

    public function getPDAMProducts() 
    {
        $pdam_products = Product::where('name', 'PDAM')
            ->get();

        dd($pdam_products);

        return response()->json([
            'status'    => 1,
            'message'   => 'Get pdam products successful',
            'pdam_products'  => $pdam_products,
        ]);
    }


    public function getNominals(Product $product) 
    {
        $nominals = [
        [
            'name'      => 'Rp 20.000',
            'price'     => 20000,
            'real_price' => 22000
        ],
        [
            'name'      => 'Rp 50.000',
            'price'     => 50000,
            'real_price' => 52000
        ],
        [
            'name'      => 'Rp 100.000',
            'price'     => 100000,
            'real_price' => 102000
        ],
        [
            'name'      => 'Rp 200.000',
            'price'     => 200000,
            'real_price' => 202000
        ],
        [
            'name'      => 'Rp 500.000',
            'price'     => 500000,
            'real_price' => 502000
        ],
        [
            'name'      => 'Rp 1.000.000',
            'price'     => 1000000,
            'real_price' => 1002000
        ]];

        return response()->json([
            'status'    => 1,
            'message'   => 'Get product nominals successful',
            'nominals'  => $nominals,
        ]);
    }


    public function checkInvoice(Request $request, Product $product) 
    {
        $validator = validator()->make($request->all(), [
            'customer_number' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $product_code   = $product->code;
        $dji_product_id = $product->dji_product_id;
        $customer_number = $request->customer_number;

        $now_id = Order::latest()->value('id') + 1;
        $reference_id = sprintf("%s%09d", $product_code, $now_id);

        $request->request->add(['dji_product_id' => $dji_product_id]);
        $request->request->add(['customer_number' => $customer_number]);
        $request->request->add(['reference_id' => $reference_id]);

        // call dji inquiry and return tagihan
        $result = app('App\Http\Controllers\DjiController')->inquiry($request)->getData();
        if (isset($result->rc) && $result->rc != '00') {
            return response()->json([
                'status'    => 0,
                'message'   => $result->rc . ': ' . trim($result->description),
            ]);
        }

        $customer_name  = trim($result->data->NM);
        $admin_fee      = (int)$result->data->AB;
        $product_price  = isset($result->data->TG) ? (int)$result->data->TG : 0;
        $order_amount   = isset($result->data->TT) ? (int)$result->data->TT : 0; 

        return response()->json([
            'status'    => 1,
            'message'   => 'Check invoice successful',
            'customer_number' => $customer_number,
            'customer_name'   => $customer_name,
            'product_price' => $product_price,
            'admin_fee'     => $admin_fee,
            'order_amount'  => $order_amount
        ]);
    }

    public function usePromoCode(Request $request, Product $product) 
    {
        $validator = validator()->make($request->all(), [
            'promo_code'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        // promo code


        return response()->json([
            'status'    => 1,
            'message'   => 'Use promo code successful',
        ]);
    }


    public function createOrder(Request $request, Product $product)
    {
        $validator = validator()->make($request->all(), [
            'product_price'     => 'required',
            'admin_fee'         => 'required',
            'customer_number'   => 'required',
            'payment_method'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $product_code    = $product->code;
        $customer_number = $request->customer_number;
        $product_price   = $request->product_price;
        $admin_fee       = $request->admin_fee;
        $discount_amount = $request->discount_amount;
        $payment_method  = $request->payment_method;
        $promo_code      = $request->promo_code;

        $order_amount   = $product_price + $admin_fee;
        $payment_amount = $order_amount - $discount_amount;

        $user    = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;
        $deposit = $user->deposit;

        if ($deposit < $payment_amount) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Saldo anda tidak mencukupi'
            ]);
        }

        $now_id = Order::latest()->value('id') + 1;
        $reference_id = sprintf("%s%09d", $product_code, $now_id);

        $order = Order::create([
            'user_id'           => $user_id,
            'product_code'      => $product_code,
            'reference_id'      => $reference_id,
            'customer_number'   => $customer_number,
            'product_price'     => $product_price,
            'admin_fee'         => $admin_fee,
            'order_amount'      => $order_amount,
            'order_status'      => 0,
            'discount_amount'   => $discount_amount,
            'payment_amount'    => $payment_amount,
            'payment_status'    => 0,
            'payment_method'    => $payment_method,
            'promo_code'        => $promo_code
        ]);

        return response()->json([
            'status'    => 1,
            'message'   => 'Create order successful',
            'order'     => $order,
        ]);
    }

    public function confirmOrder(Request $request, Product $product) 
    {
        $user    = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;

        $order_id = $request->order_id;
        $order = Order::find($order_id);

        if (!$order) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Order does not exist'
            ]);
        }

        $payment_method = $order->payment_method;

        if ($payment_method == 'Bank Transfer') {
            $validator = validator()->make($request->all(), [
                'recipient_bank_id'     => 'required',
                'sender_account_name'   => 'required',
                'sender_account_number' => 'required',
                'sender_bank_name'      => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'    => 0,
                    'message'   => $validator->errors()->first()
                ]);
            }

            BankTransfer::create([
                'order_id'              => $order_id,
                'recipient_bank_id'     => $request->recipient_bank_id,
                'sender_account_name'   => $request->sender_account_name,
                'sender_account_number' => $request->sender_account_number,
                'sender_bank_name'      => $request->sender_bank_name,
            ]);
        } 

        if ($payment_method == 'Saldo') {
            $dji_product_id = $product->dji_product_id;
            $reference_id   = $order->reference_id;
            $customer_number = $order->customer_number;
            $product_price  = $order->product_price;
            $admin_fee      = $order->admin_fee;
            $order_amount   = $order->order_amount;

            $request->request->add(['dji_product_id' => $dji_product_id]);
            $request->request->add(['customer_number' => $customer_number]);
            $request->request->add(['reference_id' => $reference_id]);
            $request->request->add(['tagihan' => $product_price]);
            $request->request->add(['admin' => $admin_fee]);
            $request->request->add(['total' => $order_amount]);

            // call dji inquiry and return tagihan
            $result = app('App\Http\Controllers\DjiController')->payment($request)->getData();
            if (isset($result->rc) && $result->rc != '00') {
                return response()->json([
                    'status'    => 0,
                    'message'   => $result->rc . ': ' . trim($result->description),
                ]);
            }

            $deposit         = $user->deposit;
            $payment_amount  = $order->payment_amount;
            $current_amount  = $deposit - $payment_amount;

            // update user deposit
            $user->deposit = $current_amount;
            $user->save();

            // update top up order status
            $order->order_status = 1;
            $order->save();

            // create deposit detail
            DepositDetail::create([
                'user_id'           => $user_id,
                'topup_order_id'    => 0,
                'order_id'          => $order_id,
                'amount'            => $payment_amount,
                'previous_amount'   => $deposit,
                'current_amount'    => $current_amount,
                'type'              => 'Payment'
            ]);

        }

        $order->payment_status = 1;
        $order->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Confirm order successful'
        ]);
    }
}
