<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use App\TopUpOrder;
use App\BalanceDetail;
use App\Order;
use App\BankTransfer;
use App\Product;
use App\Promo;
use App\CreditCardToken;
use DB;
use Jenssegers\Date\Date;
use Log;

class OrderController extends Controller
{
    public function __construct()
    {
    	$this->middleware('jwt.auth');
    }  

    public function getOrders() 
    {
        $user = JWTAuth::parseToken()->authenticate();

        $orders_arr = [];
        $orders = $user->orders()
            ->where('status', '!=', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($orders as $order) {
            $product     = Product::find($order->product_code);
            $status_text = ORDER_STATUSES[$order->status];
            $order->status_text = $status_text;

            $order_obj = [
                'order' => $order,
                'product' => $product
            ];

            $orders_arr[] = $order_obj;
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Get orders successful',
            'orders'  => $orders_arr
        ]);
    }

    public function getOrderDetails($id) 
    {
        $user = JWTAuth::parseToken()->authenticate();

        $order = $user->orders->find($id);

        if (!$order) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Get order detail failed'
            ]);
        }

        $product = Product::find($order->product_code);

        return response()->json([
            'status'  => 1,
            'message' => 'Get order details successful',
            'order'   => $order,
            'product' => $product
        ]);
    }

    private function getBill($data)
    {
        $customer_number = $data['customer_number'];
        $reference_id    = $data['reference_id'];
        $product         = $data['product'];

        $product_code       = $product->code;
        $product_category   = $product->category;
        $product_name       = $product->name;
        $dji_product_id     = $product->dji_product_id;

        $djiClient = new \App\Classes\DJIClient();
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
        $billing_period = null;

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

                        $billing_period = ucfirst(strtolower($month)) . " 20$year";
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
            $customer_name  = isset($result->data->nama) ? trim($result->data->nama) : null;
            $admin_fee      = isset($result->data->admin) ? (int)$result->data->admin : 0;
            $product_price  = isset($result->data->tagihan) ? (int)$result->data->tagihan : 0;
            $order_amount   = isset($result->data->total) ? (int)$result->data->total : 0; 

            $broken_period = $result->data->rincian[0]->periode;
            $year   = substr($broken_period, 0, 4);
            $month  = substr($broken_period, 4, 2);

            $billing_period = Date::create($year, $month)->format('F Y');

        } else if ($product_category == 'TV Kabel') {
            $customer_name  = isset($result->data->nama) ? trim($result->data->nama) : null;
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

                $billing_period = Date::create($year, $month, $date)->format('d F Y');
            }
        } else if (in_array($product_category, ['Pulsa', 'Telepon', 'Angsuran Kredit', 'Voucher Game'])) {
            $customer_name  = isset($result->data->nama) ? trim($result->data->nama) : null;
            $admin_fee      = isset($result->data->adminBank) ? (int)$result->data->adminBank : 0;
            $product_price  = isset($result->data->tagihan) ? (int)$result->data->tagihan : 0;
            $order_amount   = isset($result->data->total) ? (int)$result->data->total : 0; 
        } else if ($product_category == 'BPJS') {
            $customer_name  = isset($result->data->nama) ? trim($result->data->nama) : null;
            $admin_fee      = isset($result->data->adminBank) ? (int)$result->data->adminBank : 0;
            $order_amount   = isset($result->data->total) ? (int)$result->data->total : 0; 
            $product_price  = $order_amount - $admin_fee;
        }

        return ([
            'status'    => 1,
            'message'   => 'Get bill successful',
            'customer_number' => $customer_number,
            'customer_name'   => $customer_name,
            'product_price'   => $product_price,
            'admin_fee'       => $admin_fee,
            'order_amount'    => $order_amount,
            'billing_period'  => $billing_period
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
        $product_type      = $product->type;

        $now_id            = Order::latest()->value('id') + 1;
        $reference_id      = sprintf("PD%s%05d", date('ym'), substr($now_id, -1, 5));

        $user       = JWTAuth::parseToken()->authenticate();
        $user_id    = $user->id;

        $customer_number   = null;
        $customer_name     = null;
        $billing_period    = null;
        
        // top up saldo
        if ($product_category == 'Saldo') {
            $validator = validator()->make($request->all(), [
                'product_price' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'    => 0,
                    'message'   => $validator->errors()->first()
                ]);
            }

            $product_price  = $request->product_price;
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
            $period = $request->period;

            if (in_array($product_category, ['Pulsa', 'Paket Data']) && $product_type == 'Prepaid') {
                $customer_name = '';
                $product_price = $product->price;
                $admin_fee     = 0;
                $order_amount  = $product_price;
                $billing_period = '';
            } else {
                $bill_result = $this->getBill([
                    'customer_number' => $customer_number,
                    'reference_id'   => $reference_id,
                    'dji_product_id' => $dji_product_id,
                    'product'        => $product,
                    'period'         => $period
                ]);

                if ($bill_result['status'] == 0) {
                    return response()->json([
                        'status'    => 0,
                        'message'   => $bill_result['message']
                    ]);
                }
                
                $customer_name = ucwords(strtolower($bill_result['customer_name']));
                $product_price = $bill_result['product_price'];
                $admin_fee     = $bill_result['admin_fee'];
                $order_amount  = $bill_result['order_amount'];
                $billing_period = $bill_result['billing_period'];
            }
        }

        $order = Order::create([
            'user_id'           => $user_id,
            'product_code'      => $product_code,
            'reference_id'      => $reference_id,
            'customer_number'   => $customer_number,
            'product_price'     => $product_price,
            'admin_fee'         => $admin_fee,
            'order_amount'      => $order_amount,
            'payment_amount'    => $order_amount,
            'customer_name'     => $customer_name,
            'billing_period'    => $billing_period,
            'status'            => 1
        ]);

        return response()->json([
            'status'    => 1,
            'message'   => 'Create order successful',
            'order'     => $order,
            'product'   => $product
        ]);
    }

    public function savePromoCode(Request $request)
    {
        $order_id   = $request->order_id;
        $promo_code = strtoupper($request->promo_code);

        // user
        $user       = JWTAuth::parseToken()->authenticate();
        $user_id    = $user->id;

        // order
        $order  = Order::where('id', $order_id)
            ->where('user_id', $user_id)
            ->where('status', 1)
            ->first();
        
        if (!$order) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Order tidak ditemukan atau sudah dikonfirmasi'
            ]);
        }

        $order->status = 2;
        $order->temp_promo_code = $promo_code;
        $order->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Save promo code successful!'
        ]);
    }

    public function savePaymentMethod(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'order_id'     => 'required',
            'payment_method' => 'required|in:Balance,Bank Transfer,Credit Card'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $order_id       = $request->order_id;
        $payment_method = $request->payment_method;

        // user
        $user    = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;
        $balance = $user->balance;

        // order
        $order   = Order::where('id', $order_id)
            ->where('user_id', $user_id)
            ->whereIn('status', [1,2])
            ->first();
        
        if (!$order) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Order tidak ditemukan atau sudah dikonfirmasi'
            ]);
        }

        $product_code = $order->product_code;
        $order_amount = $order->order_amount;
        $payment_amount = $order_amount;

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

        // validation
        if ($product_category == 'Saldo' && $payment_method != 'Bank Transfer') {
            return response()->json([
                'status'    => 0,
                'message'   => 'Pembayaran untuk top up saldo hanya bisa dilakukan dengan bank transfer'
            ]);
        }

        if ($payment_method == 'Balance') {
            if ($balance < $order_amount) {
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Saldo anda tidak mencukupi'
                ]);
            }
        }

        $order->status  = 3;
        $order->payment_amount  = $payment_amount;
        $order->payment_method  = $payment_method;
        $order->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Save payment method successful!'
        ]);
    }

    public function confirmOrder(Request $request) 
    {
        $promo_code = $request->promo_code;
        $order_id   = $request->order_id;
        
        // user
        $user    = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;
        $balance = $user->balance;

        // order
        $order   = Order::where('id', $order_id)
            ->where('user_id', $user_id)
            ->where('status', 3)
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
        $payment_method = $order->payment_method;
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

        // top up saldo
        if ($product_category == 'Saldo') {
            if ($payment_method == 'Bank Transfer') {

                $validator = validator()->make($request->all(), [
                    'recipient_bank_id'     => 'required',
                    'sender_bank_id'        => 'required',
                    'sender_account_name'   => 'required',
                    'sender_account_number' => 'required'
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
                    'sender_bank_id'        => $request->sender_bank_id,
                    'sender_account_name'   => $request->sender_account_name,
                    'sender_account_number' => $request->sender_account_number
                ]);

                $order->payment_method  = $payment_method;
                $order->promo_id        = $promo_id;
                $order->discount_amount = $discount_amount;
                $order->payment_amount  = $payment_amount;
                $order->status          = 4;
                $order->save();

                $payment_due_date = date('Y-m-d H:i:s', strtotime('+1 days'));
                $order->payment_due_date = $payment_due_date;
            } else {
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Pembayaran untuk top up saldo hanya bisa dilakukan dengan bank transfer'
                ]);
            }
            
        // digital products
        } else {
            if ($payment_method == 'Balance') {
                if ($balance < $payment_amount) {
                    return response()->json([
                        'status'    => 0,
                        'message'   => 'Saldo anda tidak mencukupi'
                    ]);
                }

                $djiClient = new \App\Classes\DJIClient();
                $result = $djiClient->payment([
                    'dji_product_id'    => $dji_product_id,
                    'customer_number'   => $customer_number,
                    'reference_id'      => $reference_id, 
                    'product_price'     => $product_price,
                    'admin_fee'         => $admin_fee,
                    'order_amount'      => $order_amount,
                    'product_category'  => $product_category  
                ]);

                if (isset($result->rc) && $result->rc != '00') {
                    return response()->json([
                        'status'    => 0,
                        'message'   => $result->rc . ': ' . trim($result->description),
                    ]);
                }

                $current_amount = $balance - $payment_amount;
                $user->balance  = $current_amount;
                $user->save();

                $order->payment_method  = $payment_method;
                $order->promo_id        = $promo_id;
                $order->discount_amount = $discount_amount;
                $order->payment_amount  = $payment_amount;
                $order->status          = 6;
                $order->save();

                BalanceDetail::create([
                    'user_id'           => $user_id,
                    'topup_order_id'    => 0,
                    'order_id'          => $order_id,
                    'amount'            => $payment_amount,
                    'previous_amount'   => $balance,
                    'current_amount'    => $current_amount,
                    'type'              => 'Payment'
                ]);

            } else if ($payment_method == 'Bank Transfer') {

                $validator = validator()->make($request->all(), [
                    'recipient_bank_id'     => 'required',
                    'sender_bank_id'        => 'required',
                    'sender_account_name'   => 'required',
                    'sender_account_number' => 'required'
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
                    'sender_bank_id'        => $request->sender_bank_id,
                    'sender_account_name'   => $request->sender_account_name,
                    'sender_account_number' => $request->sender_account_number
                ]);

                $order->payment_method  = $payment_method;
                $order->promo_id        = $promo_id;
                $order->discount_amount = $discount_amount;
                $order->payment_amount  = $payment_amount;
                $order->status          = 4;
                $order->save();

            } else if ($payment_method == 'Credit Card') {

                $validator = validator()->make($request->all(), [
                    'token_id' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status'    => 0,
                        'message'   => $validator->errors()->first()
                    ]);
                }

                $token_id = $request->token_id;

                try {
                    Log::info("User $user_id is paying using Credit Card.");

                    $options['secret_api_key'] = ENV('XENDIT_SECRET_KEY');
                    $xenditPHPClient = new \XenditClient\XenditPHPClient($options);
                    $response = $xenditPHPClient->captureCreditCardPayment($reference_id, $token_id, $payment_amount);
                } catch (Exceptions $e) {
                    Log::info("User $user_id is getting Credit Card exception: " .  $e->getMessage());

                    return response()->json([
                        'status'    => 0,
                        'message'   => 'Pembayaran gagal, mohon dicoba kembali',
                    ]);
                }

                if (!isset($response['id']) || $response['status'] != 'CAPTURED') {
                    Log::info("User $user_id failed when paying with Credit Card: " . json_encode($response));

                    return response()->json([
                        'status'    => 0,
                        'message'   => 'Pembayaran gagal, mohon dicoba kembali',
                    ]);
                }

                $payment_external_id = $response['id'];

                $order->payment_external_id = $payment_external_id;
                $order->payment_method  = $payment_method;
                $order->promo_id        = $promo_id;
                $order->discount_amount = $discount_amount;
                $order->payment_amount  = $payment_amount;
                $order->status          = 5;
                $order->save();

                $djiClient = new \App\Classes\DJIClient();
                $result = $djiClient->payment([
                    'dji_product_id'    => $dji_product_id,
                    'customer_number'   => $customer_number,
                    'reference_id'      => $reference_id, 
                    'product_price'     => $product_price,
                    'admin_fee'         => $admin_fee,
                    'order_amount'      => $order_amount,
                    'product_category'  => $product_category   
                ]);

                if (isset($result->rc) && $result->rc != '00') {
                    return response()->json([
                        'status'    => 0,
                        'message'   => $result->rc . ': ' . trim($result->description),
                    ]);
                }

                $order->status  = 6;
                $order->save();
            }
        }

        return response()->json([
            'status'    => 1,
            'message'   => 'Confirm order successful'
        ]);
    }

    public function cancelOrder(Request $request) 
    {
        $order_id   = $request->order_id;
        
        // user
        $user    = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;

        // order
        $order   = Order::where('id', $order_id)
            ->where('user_id', $user_id)
            ->where('status', '!=', 6)
            ->first();
        
        if (!$order) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Order tidak ditemukan atau sudah dikonfirmasi'
            ]);
        }

        $order->cancellation_reason = 'by user';
        $order->status = 7;
        $order->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Cancel order successful'
        ]);
    }
}
