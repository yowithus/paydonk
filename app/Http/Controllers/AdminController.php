<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\TopUpOrder;
use App\TopUpBankTransfer;
use App\DepositDetail;
use App\Order;
use App\BankTransfer;
use App\Product;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    /**
     * Show the user dashboard.
     */
    public function getUsers()
    {
        return view('admin/user', [
            'page_title'    => 'User',
            'users'         => User::all()
        ]);
    }

    /**
     * Show the deposit details dashboard.
     */
    public function getDepositDetails()
    {
        return view('admin/deposit_detail', [
            'page_title'      => 'Deposit Detail',
            'deposit_details' => DepositDetail::all()
        ]);
    }


    /**
     * Show the top up orders dashboard.
     */
    public function getOrders()
    {
        return view('admin/order', [
            'page_title'    => 'Product',
            'orders'    => Order::all()
        ]);
    }

    /**
     * Verify the order.
     */
    public function verifyOrder(Request $request) 
    {
        $validator = validator()->make($request->all(), [
            'user_id'   => 'required',
            'order_id'  => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('admin/orders')->withErrors($validator);
        }

        $user_id    = $request->user_id;
        $order_id   = $request->order_id;

        $user       = User::find($user_id);
        $order      = Order::find($order_id);

        $product_code = $order->product_code;
        $product    = Product::find($product_code);
        
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

        // update top up order status
        $order->order_status = 1;
        $order->save();

        return redirect('admin/orders');
    }


    /**
     * Show the top up orders dashboard.
     */
    public function getTopUpOrders()
    {
        return view('admin/order_topup', [
            'page_title'    => 'Top up',
            'topup_orders'  => TopUpOrder::all()
        ]);
    }


    /**
     * Verify the top up order.
     */
    public function verifyTopUpOrder(Request $request) 
    {
        $validator = validator()->make($request->all(), [
            'user_id'        => 'required',
            'topup_order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('admin/topup-orders')->withErrors($validator);
        }

        $user_id     = $request->user_id;
        $user        = User::find($user_id);
        $topup_order = TopUpOrder::find($request->topup_order_id);
        
        $deposit        = $user->deposit;
        $topup_amount   = $topup_order->order_amount;
        $current_amount = $deposit + $topup_amount;

        // update user deposit
        $user->deposit = $current_amount;
        $user->save();

        // update top up order status
        $topup_order->order_status = 1;
        $topup_order->save();

        // create deposit detail
        DepositDetail::create([
            'user_id'           => $user_id,
            'topup_order_id'    => $request->topup_order_id,
            'order_id'          => 0,
            'amount'            => $topup_amount,
            'previous_amount'   => $deposit,
            'current_amount'    => $current_amount,
            'type'              => 'Top up'
        ]);

        return redirect('admin/topup-orders');
    }
}
