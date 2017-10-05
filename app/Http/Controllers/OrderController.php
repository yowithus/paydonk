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
use App\CreditCardToken;
use DB;
use Jenssegers\Date\Date;

class OrderController extends Controller
{
    public function __construct()
    {
    	$this->middleware('jwt.auth');
    }  

    private function getInquiry($data)
    {
        $customer_number = $data['customer_number'];
        $reference_id    = $data['reference_id'];
        $product         = $data['product'];

        $product_code       = $product->code;
        $product_category   = $product->category;
        $product_name       = $product->name;
        $dji_product_id     = $product->dji_product_id;

        $djiClient = new \App\Classes\DJIClient;
        $result = $djiClient->inquiry($data);
        if (isset($result->rc) && $result->rc != '00') {
            return ([
                'status'    => 0,
                'message'   => $result->rc . ': ' . trim($result->description),
            ]);
        }

        $customer_name  = null;
        $admin_fee      = 0;
        $product_price  = 0;
        $order_amount   = 0;
        $period         = null;

        if ($product_category == 'PLN') {
            $customer_name  = trim($result->data->NM);
            $admin_fee      = isset($result->data->AB) ? (int)$result->data->AB : 0;

            // Tagihan Listrik
            if ($product_code == '1101') {
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
            // Token Listrik
            } else {
                $product_price  = $product->price;
                $order_amount   = $product_price + $admin_fee;
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

        return ([
            'status'    => 1,
            'message'   => 'Get inquiry successful',
            'customer_number' => $customer_number,
            'customer_name'   => $customer_name,
            'product_price' => $product_price,
            'admin_fee'     => $admin_fee,
            'order_amount'  => $order_amount,
            'period'        => $period
        ]);
    }

    private function getPromo($data)
    {
        $promo_code  = strtoupper($data['promo_code']);
        $order_id    = $data['order_id'];

        $promo = Promo::where('code', $promo_code)
            ->first();
        
        if (!$promo) {
            return ([
                'status'    => 0,
                'message'   => 'Kode promo tidak ditemukan'
            ]);
        }

        $order = Order::where('id', $order_id)
            ->where('order_status', 0)
            ->where('payment_status', 0)
            ->first();

        if (!$order) {
            return ([
                'status'    => 0,
                'message'   => 'Order tidak ditemukan atau sudah dikonfirmasi'
            ]);
        }

        $product_price  = $order->product_price;
        $promo_id       = $promo->id;
        $discount_percentage = $promo->discount_percentage;
        $max_discount   = $promo->max_discount;
        $min_usage      = $promo->min_usage;

        if ($product_price < $min_usage) {
            return ([
                'status'    => 0,
                'message'   => "Minimum pembelian adalah Rp $min_usage"
            ]);
        }

        $discount_amount = ceil(($discount_percentage / 100) * $product_price);
        $discount_amount = ($discount_amount > $max_discount) ? $max_discount : $discount_amount;

        return ([
            'status'    => 1,
            'message'   => 'Get promo successful',
            'promo_id'  => $promo_id,
            'discount_amount' => $discount_amount
        ]);
    }

    public function usePromoCode(Request $request) 
    {
        $validator = validator()->make($request->all(), [
            'promo_code'    => 'required',
            'order_id'      => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $promo_code = $request->promo_code;
        $order_id   = $request->order_id;

        $promo_result = $this->getPromo([
            'promo_code'  => $promo_code,
            'order_id'    => $order_id
        ]);

        if ($promo_result['status'] == 0) {
            return response()->json([
                'status'    => 0,
                'message'   => $promo_result['message']
            ]);
        }

        $discount_amount = $promo_result['discount_amount'];

        return response()->json([
            'status'    => 1,
            'message'   => 'Use promo code successful',
            'discount_amount' => $discount_amount
        ]);
    }


    public function createOrder(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'product_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $product_code      = $request->product_code;

        $product = Product::where('code', $product_code)
            ->where('status', 1)
            ->first();
        
        if (!$product) {
            return ([
                'status'    => 0,
                'message'   => 'Produk tidak ditemukan'
            ]);
        }
        
        $dji_product_id    = $product->dji_product_id;
        $product_category  = $product->category;
        $reference_id      = sprintf("%07d", (Order::latest()->value('id') + 1));

        $user       = JWTAuth::parseToken()->authenticate();
        $user_id    = $user->id;

        $customer_number   = null;
        $customer_name     = null;
        $period            = null;
        
        // top up saldo
        if ($product_category == 'Saldo') {
            $product_price  = $product->price;
            $admin_fee      = 0;
            $order_amount   = $product_price + $admin_fee;

        // digital products
        } else {
            $validator = validator()->make($request->all(), [
                'customer_number'    => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'    => 0,
                    'message'   => $validator->errors()->first()
                ]);
            }

            $customer_number   = $request->customer_number;

            $inquiry_result = $this->getInquiry([
                'customer_number' => $customer_number,
                'reference_id'   => $reference_id,
                'dji_product_id' => $dji_product_id,
                'product'        => $product
            ]);

            if ($inquiry_result['status'] == 0) {
                return response()->json([
                    'status'    => 0,
                    'message'   => $inquiry_result['message']
                ]);
            }
            
            $customer_name = $inquiry_result['customer_name'];
            $product_price = $inquiry_result['product_price'];
            $admin_fee     = $inquiry_result['admin_fee'];
            $order_amount  = $inquiry_result['order_amount'];
            $period        = $inquiry_result['period'];
        }

        $order = Order::create([
            'user_id'           => $user_id,
            'product_code'      => $product_code,
            'reference_id'      => $reference_id,
            'customer_number'   => $customer_number,
            'product_price'     => $product_price,
            'admin_fee'         => $admin_fee,
            'order_amount'      => $order_amount,
            'payment_amount'    => $order_amount
        ]);

        return response()->json([
            'status'    => 1,
            'message'   => 'Create order successful',
            'order'     => $order,
            'period'    => $period,
            'customer_name' => $customer_name
        ]);
    }

    public function confirmOrder(Request $request) 
    {
        $validator = validator()->make($request->all(), [
            'order_id'     => 'required',
            'payment_method' => 'required|in:Saldo,Bank Transfer,Credit Card'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $order_id       = $request->order_id;
        $payment_method = $request->payment_method;
        $promo_code     = $request->promo_code;
        
        // order
        $order    = Order::where('id', $order_id)
            ->where('order_status', 0)
            ->where('payment_status', 0)
            ->first();
        
        if (!$order) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Order tidak ditemukan atau sudah dikonfirmasi'
            ]);
        }

        $product_code   = $order->product_code;
        $reference_id   = $order->reference_id;
        $customer_number = $order->customer_number;
        $product_price  = $order->product_price;
        $admin_fee      = $order->admin_fee;
        $order_amount   = $order->order_amount;
        $promo_id       = null;
        
        // promo
        if ($promo_code) {
            $promo_result = $this->getPromo([
                'promo_code'  => $promo_code,
                'order_id'    => $order_id
            ]);

            if ($promo_result['status'] == 0) {
                return response()->json([
                    'status'    => 0,
                    'message'   => $promo_result['message']
                ]);
            }

            $promo_id  = $promo_result['promo_id'];
            $discount_amount = $promo_result['discount_amount'];
        } else {
            $discount_amount = 0;
        }

        $payment_amount = $order_amount - $discount_amount;

        // product
        $product    = Product::where('code', $product_code)
            ->where('status', 1)
            ->first();
        
        if (!$product) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Produk tidak ditemukan'
            ]);
        }

        $product_category   = $product->category;
        $dji_product_id     = $product->dji_product_id;

        // user
        $user           = JWTAuth::parseToken()->authenticate();
        $user_id        = $user->id;
        $deposit        = $user->deposit;

        // top up saldo
        if ($product_category == 'Saldo') {
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

                $order->payment_method = $payment_method;
                $order->promo_id = $promo_id;
                $order->discount_amount = $discount_amount;
                $order->payment_amount = $payment_amount;
                $order->payment_status = 1;
                $order->save();

                // if ($payment_method == 'Bank Transfer') {
                //     $transfer_deadline = date('Y-m-d H:i:s', strtotime('+1 days'));
                //     $order->transfer_deadline = $transfer_deadline;
                // }
            } else {
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Pembayaran untuk top up saldo hanya bisa dilakukan dengan bank transfer'
                ]);
            }
            
        // digital products
        } else {
            if ($payment_method == 'Saldo') {
                if ($deposit < $payment_amount) {
                    return response()->json([
                        'status'    => 0,
                        'message'   => 'Saldo anda tidak mencukupi'
                    ]);
                }

                $djiClient = new \App\Classes\DJIClient;
                $result = $djiClient->payment([
                    'dji_product_id'    => $dji_product_id,
                    'customer_number'   => $customer_number,
                    'reference_id'      => $reference_id, 
                    'product_price'     => $product_price,
                    'admin_fee'         => $admin_fee,
                    'order_amount'      => $order_amount    
                ]);

                if (isset($result->rc) && $result->rc != '00') {
                    return response()->json([
                        'status'    => 0,
                        'message'   => $result->rc . ': ' . trim($result->description),
                    ]);
                }

                $current_amount  = $deposit - $payment_amount;
                $user->deposit = $current_amount;
                $user->save();

                $order->payment_method = $payment_method;
                $order->promo_id = $promo_id;
                $order->discount_amount = $discount_amount;
                $order->payment_amount = $payment_amount;
                $order->payment_status = 1;
                $order->order_status = 1;
                $order->save();

                DepositDetail::create([
                    'user_id'           => $user_id,
                    'topup_order_id'    => 0,
                    'order_id'          => $order_id,
                    'amount'            => $payment_amount,
                    'previous_amount'   => $deposit,
                    'current_amount'    => $current_amount,
                    'type'              => 'Payment'
                ]);

            } else if ($payment_method == 'Bank Transfer') {

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

                $order->payment_method = $payment_method;
                $order->promo_id = $promo_id;
                $order->discount_amount = $discount_amount;
                $order->payment_amount = $payment_amount;
                $order->payment_status = 1;
                $order->save();

            } else if ($payment_method == 'Credit Card') {

                $validator = validator()->make($request->all(), [
                    'token_id' => 'required',
                    'credit_card_number' => 'required',
                    'credit_card_type'   => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status'    => 0,
                        'message'   => $validator->errors()->first()
                    ]);
                }

                $token_id = $request->token_id;
                $credit_card_number = $request->credit_card_number;
                $credit_card_type   = $request->credit_card_type;

                $payment_reference_id = ENV('XENDIT_PREFIX') . $reference_id;
                $options['secret_api_key'] = ENV('XENDIT_SECRET_KEY');
                $xenditPHPClient = new \XenditClient\XenditPHPClient($options);
                $response = $xenditPHPClient->captureCreditCardPayment($payment_reference_id, $token_id, $payment_amount);

                if (!isset($response['id']) || $response['status'] != 'CAPTURED') {
                    return response()->json([
                        'status'    => 0,
                        'message'   => 'Pembayaran gagal, mohon dicoba kembali',
                    ]);
                }

                $payment_external_id = $response['id'];
                
                CreditCardToken::updateOrCreate([
                    'user_id' => $user_id
                ], [
                    'token_id'  => $token_id,
                    'credit_card_number' => $credit_card_number, 
                    'credit_card_type'   => $credit_card_type
                ]);

                $order->payment_external_id = $payment_external_id;
                $order->payment_method = $payment_method;
                $order->promo_id = $promo_id;
                $order->discount_amount = $discount_amount;
                $order->payment_amount = $payment_amount;
                $order->payment_status = 1;
                $order->save();

                $djiClient = new \App\Classes\DJIClient;
                $result = $djiClient->payment([
                    'dji_product_id'    => $dji_product_id,
                    'customer_number'   => $customer_number,
                    'reference_id'      => $reference_id, 
                    'product_price'     => $product_price,
                    'admin_fee'         => $admin_fee,
                    'order_amount'      => $order_amount    
                ]);

                if (isset($result->rc) && $result->rc != '00') {
                    return response()->json([
                        'status'    => 0,
                        'message'   => $result->rc . ': ' . trim($result->description),
                    ]);
                }

                $order->order_status = 1;
                $order->save();
            }
        }

        return response()->json([
            'status'    => 1,
            'message'   => 'Confirm order successful'
        ]);
    }
}
