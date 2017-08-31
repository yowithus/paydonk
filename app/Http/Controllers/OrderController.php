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
use App\Promo;
use DB;
use Jenssegers\Date\Date;

class OrderController extends Controller
{
    public function __construct()
    {
    	$this->middleware('jwt.auth', ['except' => [
            'getRecipientBanks', 
            'getSenderBanks', 
            'getTopUpNominals', 
            'getPLNProducts', 
            'getPDAMProducts',
            'getTVProducts'
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
        $reference_id = sprintf("%07d", $now_id);

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

        $topup_order = TopUpOrder::where('id', $request->topup_order_id)
            ->where('payment_status', 0)
            ->first();

        if (!$topup_order) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Top up not found or already confirmed'
            ]);
        }

        $topup_order->payment_status = 1;
        $topup_order->save();

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

    public function getPLNProducts() 
    {
        $pln_products = Product::selectRaw('variant_name as name, price')
            ->where('category', 'PLN')
            ->where('type', 'Prepaid')
            ->get();

        return response()->json([
            'status'    => 1,
            'message'   => 'Get token listrik products successful',
            'pln_products'  => $pln_products,
        ]);
    }

    public function getPDAMProducts() 
    {
        $pdam_products = Product::selectRaw('variant_name as name, province, region, code')
            ->where('category', 'PDAM')
            ->get();

        foreach ($pdam_products as $pdam_product) {
            if ($pdam_product->region == 'AETRA') {
                $pdam_product->image_name = 'aetra.png';
            } else if ($pdam_product->region == 'PALYJA') {
                $pdam_product->image_name = 'palyja.png';
            } else {
                $pdam_product->image_name = 'pam.png';
            }
        }

        return response()->json([
            'status'    => 1,
            'message'   => 'Get pdam products successful',
            'pdam_products'  => $pdam_products,
        ]);
    }

    public function getTVProducts() 
    {
        $tv_products = Product::selectRaw('name, variant_name, code, type')
            ->where('category', 'TV Kabel')
            ->get();

        return response()->json([
            'status'    => 1,
            'message'   => 'Get tv kabel products successful',
            'tv_products'  => $tv_products,
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

        // call dji inquiry and return tagihan
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
                $admin_fee      = (int)$result->data->adminBank;
                $product_price  = (int)$result->data->tagihan;
                $order_amount   = (int)$result->data->total; 
            }
            // Indovision
            else if ($product_code == '1302') {
                $admin_fee      = (int)$result->data->adminBank;
                $product_price  = (int)$result->data->tagihan;
                $order_amount   = (int)$result->data->total; 
                $broken_period  = str_replace('/', '-', $result->data->periodeAkhir);
            }
            // Nex Media
            else if ($product_code == '1305') {
                $admin_fee      = (int)$result->data->adminBank;
                $product_price  = (int)$result->data->tagihan;
                $order_amount   = (int)$result->data->total; 
                $broken_period  = $result->data->jatuhTempo;
            } 
            // K-Vision
            else if ($product_code == '1306') {
                $admin_fee      = 0;
                $product_price  = (int)$result->data->saldo;
                $order_amount   = (int)$result->data->saldo;
            }
            // Orange TV Postpaid
            else if ($product_code == '1307') {
                $admin_fee      = 0;
                $product_price  = (int)$result->data->totalTagihan;
                $order_amount   = (int)$result->data->totalTagihan; 
                $broken_period  = $result->data->jatuhTempo;
            } 
            // Orange TV Prepaid
            else if (in_array($product_code, ['1308', '1309', '1310', '1311'])) {
                $admin_fee      = 0;
                $product_price  = (int)$result->data->harga;
                $order_amount   = (int)$result->data->harga; 
            } 
            // Skynindo
            else if (in_array($product_code, ['1312', '1313', '1314', '1315', '1316', '1317', '1318', '1319', '1320', '1321', '1322'])) {
                $admin_fee      = 0;
                $product_price  = 0;
                $order_amount   = 0; 
            }
            
            

            if ($broken_period) {
                $broken_periods = explode('-', $broken_period);

                $date   = $broken_periods[0];
                $month  = $broken_periods[1];
                $year   = $broken_periods[2];

                $period = Date::create($year, $month, $date)->format('d F Y');
            }
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
        $validator = validator()->make($request->all(), [
            'product_price' => 'required',
            'admin_fee'     => 'required',
            'promo_code'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $product_price  = $request->product_price;
        $promo_code     = $request->promo_code;

        $promo = Promo::find($promo_code);
        if (!$promo) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Promo tidak ditemukan'
            ]);
        }

        $discount_percentage = $promo->discount_percentage;
        $max_discount = $promo->max_discount;
        $min_usage = $promo->min_usage;

        if ($product_price < $min_usage) {
            return response()->json([
                'status'    => 0,
                'message'   => "Minimum pembelian adalah Rp $min_usage"
            ]);
        }

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

        if ($payment_method == 'Saldo') {
            if ($deposit < $payment_amount) {
                return response()->json([
                    'status'    => 0,
                    'message'   => 'Saldo anda tidak mencukupi'
                ]);
            }
        } else if ($payment_method == 'Bank Transfer') {

        } else {
            return response()->json([
                'status'    => 0,
                'message'   => 'Metode pembayaran tidak ada'
            ]);
        }

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
            'promo_code'        => $promo_code
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
        } else if ($payment_method == 'Saldo') {
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
