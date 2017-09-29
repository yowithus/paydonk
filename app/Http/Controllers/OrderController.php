<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use App\TopUpOrder;
use App\DepositDetail;
use App\Order;
use App\BankTransfer;
use App\Product;
use App\Promo;
use DB;
use Jenssegers\Date\Date;

class OrderController extends Controller
{
    public function __construct()
    {
    	$this->middleware('jwt.auth');
    }  

    public function checkInvoice(Request $request, Product $product) 
    {
        // validate request data
        $validator = validator()->make($request->all(), [
            'customer_number' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $product_code       = $product->code;
        $product_category   = $product->category;
        $product_name       = $product->name;
        $dji_product_id     = $product->dji_product_id;
        $customer_number    = $request->customer_number;

        $now_id = Order::latest()->value('id') + 1;
        $reference_id = sprintf("%s%09d", $product_code, $now_id);

        $request->request->add(['dji_product_id' => $dji_product_id]);
        $request->request->add(['customer_number' => $customer_number]);
        $request->request->add(['reference_id' => $reference_id]);

        // hit dji inquiry
        $result = app('App\Http\Controllers\DjiController')->inquiry($request)->getData();
        if (isset($result->rc) && $result->rc != '00') {

            $error_message = isset($result->description) ? ucfirst(strtolower(trim($result->description))) : 'Terjadi kendala pada server, silakan coba beberapa saat lagi';
            if ($result->rc == '04') {
                $error_message = 'ID Pelanggan tidak ditemukan';
            }
            return response()->json([
                'status'    => 0,
                'message'   => $error_message,
            ]);
        }

        $customer_name  = '';
        $admin_fee      = 0;
        $product_price  = 0;
        $order_amount   = 0;
        $period         = '';

        if ($product_category == 'PLN') {
            $customer_name  = trim($result->data->NM);
            $admin_fee      = isset($result->data->AB) ? (int)$result->data->AB : 0;
            $product_price  = isset($result->data->TG) ? (int)$result->data->TG : 0;
            $order_amount   = isset($result->data->TT) ? (int)$result->data->TT : 0; 

            for ($i=1; $i<=4; $i++) {
                if (isset($result->data->{"I$i"})) {
                    $broken_period = $result->data->{"I$i"}->BT;
                    $month  = preg_replace("/[^A-Z]+/", "", $broken_period);
                    $year   = preg_replace("/[^0-9]/","", $broken_period);

                    $period = ucfirst(strtolower($month)) . " 20$year";
                } else {
                    break;
                }
            }

        } else if ($product_category == 'PDAM') {
            $customer_name  = trim($result->data->nama);
            $admin_fee      = (int)$result->data->admin;
            $product_price  = (int)$result->data->tagihan;
            $order_amount   = (int)$result->data->total; 

            $broken_period = $result->data->rincian[0]->periode;
            $year   = substr($broken_period, 0, 4);
            $month  = substr($broken_period, 4, 2);

            $period = Date::create($year, $month)->format('F Y');

        } else if ($product_category == 'TV Kabel') {
            $customer_name  = isset($result->data->nama) ? trim($result->data->nama) : '';
            $broken_period  = '';

            // Transvision & Big TV & Topas TV
            if (in_array($product_code, ['1301', '1303', '1304'])) {
                $admin_fee      = isset($result->data->adminBank) ? (int)$result->data->adminBank : 0;
                $product_price  = isset($result->data->tagihan) ? (int)$result->data->tagihan : 0;
                $order_amount   = isset($result->data->total) ? (int)$result->data->total : 0; 
            }
            // Indovision
            else if ($product_code == '1302') {
                $admin_fee      = isset($result->data->adminBank) ? (int)$result->data->adminBank : 0;
                $product_price  = isset($result->data->tagihan) ? (int)$result->data->tagihan : 0;
                $order_amount   = isset($result->data->total) ? (int)$result->data->total : 0; 
                $broken_period  = str_replace('/', '-', $result->data->periodeAkhir);
            }
            // Nex Media
            else if ($product_code == '1305') {
                $admin_fee      = isset($result->data->adminBank) ? (int)$result->data->adminBank : 0;
                $product_price  = isset($result->data->tagihan) ? (int)$result->data->tagihan : 0;
                $order_amount   = isset($result->data->total) ? (int)$result->data->total : 0;  
                $broken_period  = $result->data->jatuhTempo;
            } 
            // K-Vision
            else if ($product_code == '1306') {
                $admin_fee      = 0;
                $product_price  = 0;
                $order_amount   = 0;
            }
            // Orange TV Postpaid
            else if ($product_code == '1307') {
                $admin_fee      = 0;
                $product_price  = isset($result->data->totalTagihan) ? (int)$result->data->totalTagihan : 0;
                $order_amount   = $product_price; 
                $broken_period  = $result->data->jatuhTempo;
            } 
            // Orange TV Prepaid
            else if (in_array($product_code, ['1308', '1309', '1310', '1311'])) {
                $admin_fee      = 0;
                $product_price  = isset($result->data->harga) ? (int)$result->data->harga : 0;
                $order_amount   = $product_price; 
            } 
            // Skynindo
            else if (in_array($product_code, ['1312', '1313', '1314', '1315', '1316', '1317', '1318', '1319', '1320', '1321', '1322'])) {
                $admin_fee      = 0;
                $product_price  = isset($result->data->harga) ? (int)$result->data->harga : 0;
                $order_amount   = $product_price; 
            }
            
            if ($broken_period) {
                $broken_periods = explode('-', $broken_period);

                $date   = $broken_periods[0];
                $month  = $broken_periods[1];
                $year   = $broken_periods[2];

                $period = Date::create($year, $month, $date)->format('d F Y');
            }
        } else if ($product_category == 'Pulsa' || $product_category == 'Telepon' || $product_category == 'Angsuran Kredit') {
            $customer_name  = isset($result->data->nama) ? trim($result->data->nama) : '';
            $admin_fee      = isset($result->data->adminBank) ? (int)$result->data->adminBank : 0;
            $product_price  = isset($result->data->tagihan) ? (int)$result->data->tagihan : 0;
            $order_amount   = isset($result->data->total) ? (int)$result->data->total : 0; 
        } else if ($product_category == 'BPJS') {
            $customer_name  = isset($result->data->nama) ? trim($result->data->nama) : '';
            $admin_fee      = isset($result->data->adminBank) ? (int)$result->data->adminBank : 0;
            $order_amount   = isset($result->data->total) ? (int)$result->data->total : 0; 
            $product_price  = $order_amount - $admin_fee;
        }

        return response()->json([
            'status'    => 1,
            'message'   => 'Check invoice successful',
            'customer_number' => $customer_number,
            'customer_name'   => $customer_name,
            'product_price' => $product_price,
            'admin_fee'     => $admin_fee,
            'order_amount'  => $order_amount,
            'period'        => $period
        ]);
    }

    public function usePromoCode(Request $request, Product $product) 
    {
        // validate request data
        $validator = validator()->make($request->all(), [
            'promo_code'    => 'required',
            'product_price' => 'required',
            'admin_fee'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $product_price  = $request->product_price;
        $promo_code     = strtoupper($request->promo_code);

        // validate promo
        $promo = Promo::where('code', '=', $promo_code)->first();
        if (!$promo) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Kode promo tidak ditemukan'
            ]);
        }

        $discount_percentage = $promo->discount_percentage;
        $max_discount   = $promo->max_discount;
        $min_usage      = $promo->min_usage;

        if ($product_price < $min_usage) {
            return response()->json([
                'status'    => 0,
                'message'   => "Minimum pembelian adalah Rp $min_usage"
            ]);
        }

        // calculate discount amount
        $discount_amount = ceil(($discount_percentage / 100) * $product_price);
        $discount_amount = ($discount_amount > $max_discount) ? $max_discount : $discount_amount;

        return response()->json([
            'status'    => 1,
            'message'   => 'Use promo code successful',
            'discount_amount' => $discount_amount
        ]);
    }


    public function createOrder(Request $request, Product $product)
    {
        $product_code    = $product->code;
        $product_category = $product->category;

        // top up
        if ($product_category == 'Saldo') {
            // validate request data
            $validator = validator()->make($request->all(), [
                'payment_method'    => 'required|in:Bank Transfer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'    => 0,
                    'message'   => $validator->errors()->first()
                ]);
            }

            $product_price  = $product->price;
            $admin_fee      = 0;
        
        // product
        } else {
             // validate request data
            $validator = validator()->make($request->all(), [
                'customer_number'   => 'required',
                'product_price'     => 'required',
                'admin_fee'         => 'required',
                'payment_method'    => 'required|in:Saldo,Bank Transfer,Credit Card',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'    => 0,
                    'message'   => $validator->errors()->first()
                ]);
            }

            $product_price   = $request->product_price;
            $admin_fee       = $request->admin_fee;
        }

        $customer_number = $request->customer_number;
        $payment_method  = $request->payment_method;
        $promo_code      = strtoupper($request->promo_code);
        $order_amount    = $product_price + $admin_fee;
        $payment_amount  = $order_amount;
        $discount_amount = 0;
        $promo_id        = null;

        $user    = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;

        // validate promo
        if ($promo_code) {
            $promo = Promo::where('code', '=', $promo_code)->first();
            if (!$promo) {
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Kode promo tidak ditemukan'
                ]);
            }

            $promo_id       = $promo->id;
            $discount_percentage = $promo->discount_percentage;
            $max_discount   = $promo->max_discount;
            $min_usage      = $promo->min_usage;

            if ($product_price < $min_usage) {
                return response()->json([
                    'status'    => 0,
                    'message'   => "Minimum pembelian adalah Rp $min_usage"
                ]);
            }

            // calculate discount & payment amount
            $discount_amount = ceil(($discount_percentage / 100) * $product_price);
            $discount_amount = ($discount_amount > $max_discount) ? $max_discount : $discount_amount;

            $payment_amount = $order_amount - $discount_amount;
        }

        // validate saldo balance
        if ($payment_method == 'Saldo') {
            $deposit = $user->deposit;

            if ($deposit < $payment_amount) {
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Saldo anda tidak mencukupi'
                ]);
            }
        }

        // create order
        $now_id = Order::latest()->value('id') + 1;
        $reference_id = sprintf("%07d", $now_id);

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
            'promo_id'          => $promo_id
        ]);

        if ($payment_method == 'Bank Transfer') {
            $transfer_deadline = date('Y-m-d H:i:s', strtotime('+1 days'));
            $order->transfer_deadline = $transfer_deadline;
        }

        return response()->json([
            'status'    => 1,
            'message'   => 'Create order successful',
            'order'     => $order,
        ]);
    }

    public function confirmOrder(Request $request, Product $product) 
    {
        // validate request data
        $validator = validator()->make($request->all(), [
            'order_id'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        // user
        $user    = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;
        $deposit = $user->deposit;

        // order
        $order_id       = $request->order_id;
        $order          = Order::find($order_id);
        
        if (!$order) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Order tidak ditemukan'
            ]);
        }

        $product_category = $product->category;
        $reference_id   = $order->reference_id;
        $customer_number = $order->customer_number;
        $product_price  = $order->product_price;
        $admin_fee      = $order->admin_fee;
        $order_amount   = $order->order_amount;
        $payment_amount = $order->payment_amount;
        $payment_method = $order->payment_method;


        if ($payment_method == 'Bank Transfer') {
            // validate bank data
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

            // insert to bank transfer
            BankTransfer::create([
                'order_id'              => $order_id,
                'recipient_bank_id'     => $request->recipient_bank_id,
                'sender_account_name'   => $request->sender_account_name,
                'sender_account_number' => $request->sender_account_number,
                'sender_bank_name'      => $request->sender_bank_name,
            ]);

        } else {
            $dji_product_id = $product->dji_product_id;
            
            $request->request->add(['dji_product_id' => $dji_product_id]);
            $request->request->add(['customer_number' => $customer_number]);
            $request->request->add(['reference_id' => $reference_id]);
            $request->request->add(['tagihan' => $product_price]);
            $request->request->add(['admin' => $admin_fee]);
            $request->request->add(['total' => $order_amount]);


            if ($payment_method == 'Saldo') {
                // validate saldo balance
                if ($deposit < $payment_amount) {
                    return response()->json([
                        'status'    => 0,
                        'message'   => 'Saldo anda tidak mencukupi'
                    ]);
                }

                // hit dji
                $result = app('App\Http\Controllers\DjiController')->payment($request)->getData();
                if (isset($result->rc) && $result->rc != '00') {
                    return response()->json([
                        'status'    => 0,
                        'message'   => $result->rc . ': ' . trim($result->description),
                    ]);
                }

                // update deposit
                $current_amount  = $deposit - $payment_amount;
                $user->deposit = $current_amount;
                $user->save();

                // insert to deposit detail
                DepositDetail::create([
                    'user_id'           => $user_id,
                    'topup_order_id'    => 0,
                    'order_id'          => $order_id,
                    'amount'            => $payment_amount,
                    'previous_amount'   => $deposit,
                    'current_amount'    => $current_amount,
                    'type'              => 'Payment'
                ]);

            } else if ($payment_method == 'Credit Card') {
                // validate credit card token
                $validator = validator()->make($request->all(), [
                    'token_id'     => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status'    => 0,
                        'message'   => $validator->errors()->first()
                    ]);
                }

                // hit xendit
                $token_id = $request->token_id;
                $reference_id = ENV('XENDIT_PREFIX') . $reference_id;
                $options['secret_api_key'] = ENV('XENDIT_SECRET_KEY');
                $xenditPHPClient = new \XenditClient\XenditPHPClient($options);
                $response = $xenditPHPClient->captureCreditCardPayment($reference_id, $token_id, $payment_amount);

                if (!isset($response['id']) || $response['status'] != 'CAPTURED') {
                    return response()->json([
                        'status'    => 0,
                        'message'   => 'Pembayaran gagal, mohon dicoba kembali',
                    ]);
                }

                $payment_external_id = $response['id'];
                $order->payment_external_id = $payment_external_id;

                // hit dji
                $result = app('App\Http\Controllers\DjiController')->payment($request)->getData();
                if (isset($result->rc) && $result->rc != '00') {
                    return response()->json([
                        'status'    => 0,
                        'message'   => $result->rc . ': ' . trim($result->description),
                    ]);
                }
            }

            $order->order_status = 1;
            $order->save();
        }

        $order->payment_status = 1;
        $order->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Confirm order successful'
        ]);
    }
}
