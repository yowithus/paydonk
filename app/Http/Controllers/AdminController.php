<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\DepositDetail;
use App\Order;
use App\Product;
use App\RecipientBank;
use App\SenderBank;
use Carbon\Carbon;
use DB;

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
        $today      = Carbon::now();
        $last_month = Carbon::now()->subDays(30);

        $users_count = User::whereBetween('created_at', [$last_month, $today])
            ->count();

        $topup_orders_count = Order::where('order_status', 1)
            ->where('product_code', 'like', '%10%')
            ->whereBetween('created_at', [$last_month, $today])
            ->count();

        $orders_count = Order::where('order_status', 1)
            ->where('product_code', 'not like', '%10%')
            ->whereBetween('created_at', [$last_month, $today])
            ->count();

        $total_revenue = Order::select(DB::raw('sum(payment_amount) as total_revenue'))
            ->where('order_status', 1)
            ->where('payment_status', 1)
            ->whereBetween('created_at', [$last_month, $today])
            ->value('total_revenue');

        return view('admin/dashboard', [
            'page_title'    => 'Monthly Summary',
            'today'         => $today,
            'last_month'    => $last_month,
            'users_count'   => $users_count,
            'topup_orders_count'  => $topup_orders_count,
            'orders_count'  => $orders_count,
            'total_revenue' => $total_revenue,
        ]);
    }

    /**
     * Show monthly statistic.
     */
    public function getMonthlyStatistic(Request $request)
    {
        $today      = Carbon::now();
        $last_month = Carbon::now()->subDays(30);

        $sales = Order::select(DB::raw('date(created_at) as date, sum(payment_amount) as revenue'))
            ->where('order_status', 1)
            ->whereBetween('created_at', [$last_month, $today])
            ->groupBy('date')
            ->pluck('revenue', 'date')
            ->toArray();

        $total_revenue      = array_sum($sales);
        $total_cost         = 0.9 * $total_revenue;
        $total_profit       = $total_revenue - $total_cost;

        return array(
            'sales'     => $sales,
            'total_revenue' => $total_revenue,
            'total_cost'    => $total_cost,
            'total_profit'  => $total_profit
        );
    }

    /**
     * Show the users dashboard.
     */
    public function getUsers(Request $request)
    {
        $email         = $request->email;
        $phone_number  = $request->phone_number;
        $join_date     = $request->join_date;

        $users = User::orderBy('created_at', 'desc');

        if ($email) {
            $users->where('email', $email);
        }

        if ($phone_number) {
            $users->where('phone_number', $phone_number);
        }

        if ($join_date) {
            $start_date     = substr($join_date, 0, 10);
            $end_date       = substr($join_date, 13, 19);

            $users->whereDate('created_at', '>=', $start_date);
            $users->whereDate('created_at', '<=', $end_date);
        }

        $users = $users->paginate(10);
        $users->withPath("/admin/users?email=$email&phone_number=$phone_number&join_date=$join_date");

        return view('admin/user', [
            'page_title'    => 'Users',
            'users'         => $users
        ]);
    }

    /**
     * Update user status.
     */
    public function updateStatusUser(Request $request, User $user)
    {   
        $status = $request->status ? 1 : 0;

        $user->update([
            'status' => $status,
        ]);

        return redirect('admin/users');
    }

    /**
     * Show the deposit details dashboard.
     */
    public function getDepositDetails(Request $request)
    {
        $email         = $request->email;
        $phone_number  = $request->phone_number;
        $deposit_date  = $request->deposit_date;

        $deposit_details = DepositDetail::join('users', 'deposit_details.user_id', '=', 'users.id')
            ->select('deposit_details.*')
            ->orderBy('created_at', 'desc');

        if ($email) {
            $deposit_details->where('users.email', $email);
        }

        if ($phone_number) {
            $deposit_details->where('users.phone_number', $phone_number);
        }

        if ($deposit_date) {
            $start_date     = substr($deposit_date, 0, 10);
            $end_date       = substr($deposit_date, 13, 19);

            $deposit_details->whereDate('deposit_details.created_at', '>=', $start_date);
            $deposit_details->whereDate('deposit_details.created_at', '<=', $end_date);
        }

        $deposit_details = $deposit_details->paginate(10);
        $deposit_details->withPath("/admin/deposit-details?email=$email&phone_number=$phone_number&deposit_date=$deposit_date");

        return view('admin/deposit_detail', [
            'page_title'      => 'Deposit Details',
            'deposit_details' => $deposit_details
        ]);
    }


    /**
     * Show the orders dashboard.
     */
    public function getOrders(Request $request)
    {
        $reference_id   = $request->reference_id;
        $email          = $request->email;
        $order_status   = $request->order_status;
        $payment_status = $request->payment_status;
        $order_date     = $request->order_date;

        $orders = Order::join('users', 'orders.user_id', '=', 'users.id')
            ->select('orders.*', 'users.email')
            ->orderBy('orders.created_at', 'desc');
            
        if ($reference_id) {
            $orders->where('orders.reference_id', $reference_id);
        }

        if ($email) {
            $orders->where('users.email', $email);
        }

        if ($order_status) {
            $orders->where('orders.order_status', $order_status);
        } else {
            $orders->where('orders.order_status', 0);
        }

        if ($payment_status) {
            $orders->where('orders.payment_status', $payment_status);
        } else {
            $orders->where('orders.payment_status', 1);
        }

        if ($order_date) {
            $start_date     = substr($order_date, 0, 10);
            $end_date       = substr($order_date, 13, 19);

            $orders->whereDate('orders.created_at', '>=', $start_date);
            $orders->whereDate('orders.created_at', '<=', $end_date);
        }

        $orders = $orders->paginate(10);
        $orders->withPath("/admin/orders?reference_id=$reference_id&email=$email&order_status=$order_status&payment_status=$payment_status&order_date=$order_date");

        return view('admin/order', [
            'page_title'    => 'Orders',
            'orders'  => $orders
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
        $product_category    = $product->category;

        if ($product_category == 'Saldo') {
            $deposit        = $user->deposit;
            $topup_amount   = $order->order_amount;
            $current_amount = $deposit + $topup_amount;

            // update user deposit
            $user->deposit = $current_amount;
            $user->save();

            // create deposit detail
            DepositDetail::create([
                'user_id'           => $user_id,
                'order_id'          => $order_id,
                'amount'            => $topup_amount,
                'previous_amount'   => $deposit,
                'current_amount'    => $current_amount,
                'type'              => 'Top up'
            ]);

        } else {
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
        }

        // update order status
        $order->order_status = 1;
        $order->save();

        return redirect('admin/orders');
    }


    /**
     * Show the products dashboard.
     */
    public function getProducts(Request $request)
    {
        $category   = $request->category;
        $type       = $request->type;

        $products = Product::orderBy('name', 'asc');

        if ($category) {
            $products->where('category', $category);
        } else {
            $products->where('category', 'Saldo');
        }

        if ($type && $type != 'All') {
            $products->where('type', $type);
        }

        $products = $products->paginate(10);
        $products->withPath("/admin/products?category=$category&type=$type");

        return view('admin/product', [
            'page_title'    => 'Products',
            'products'      => $products
        ]);
    }

    /**
     * Update product status.
     */
    public function updateStatusProduct(Request $request, Product $product)
    {   
        $status = $request->status ? 1 : 0;

        $product->update([
            'status' => $status,
        ]);

        return redirect('admin/products');
    }

    /**
     * Show the recipient banks dashboard.
     */
    public function getRecipientBanks(Request $request)
    {
        $recipient_banks = DB::table('recipient_banks')
            ->get();        

        return view('admin/recipient_bank', [
            'page_title'    => 'Recipient Banks',
            'recipient_banks' => $recipient_banks,
        ]);
    }

    /**
     * Update recipient bank status.
     */
    public function updateStatusRecipientBank(Request $request, RecipientBank $recipient_bank)
    {   
        $status = $request->status ? 1 : 0;

        $recipient_bank->update([
            'status' => $status,
        ]);

        return redirect('admin/recipient-banks');
    }

    /**
     * Show the sender banks dashboard.
     */
    public function getSenderBanks(Request $request)
    {
        $sender_banks = DB::table('sender_banks')
            ->get();

        return view('admin/sender_bank', [
            'page_title'    => 'Sender Banks',
            'sender_banks' => $sender_banks,
        ]);
    }

    /**
     * Update sender bank status.
     */
    public function updateStatusSenderBank(Request $request, SenderBank $sender_bank)
    {   
        $status = $request->status ? 1 : 0;

        $sender_bank->update([
            'status' => $status,
        ]);

        return redirect('admin/sender-banks');
    }
}
