<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\BalanceDetail;
use App\Order;
use App\Product;
use App\RecipientBank;
use App\SenderBank;
use App\Refund;
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

        $topup_orders_count = Order::where('status', ORDER_STATUSES['completed'])
            ->where('product_code', 'like', '10%')
            ->whereBetween('created_at', [$last_month, $today])
            ->count();

        $orders_count = Order::where('status', ORDER_STATUSES['completed'])
            ->where('product_code', 'not like', '10%')
            ->whereBetween('created_at', [$last_month, $today])
            ->count();
 
        $total_revenue = Order::select(DB::raw('sum(payment_amount) as total_revenue'))
            ->where('status', ORDER_STATUSES['completed'])
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
            ->where('status', ORDER_STATUSES['completed'])
            ->whereBetween('created_at', [$last_month, $today])
            ->groupBy('date')
            ->pluck('revenue', 'date')
            ->toArray();

        return [
            'sales' => $sales
        ];
    }

    /**
     * Show category statistic.
     */
    public function getCategoryStatistic(Request $request)
    {
        $today      = Carbon::now();
        $last_month = Carbon::now()->subDays(30);

        $sales = Order::select(DB::raw('products.category, sum(orders.payment_amount) as revenue'))
            ->join('products', 'orders.product_code', '=', 'products.code')
            ->where('orders.status', ORDER_STATUSES['completed'])
            ->whereBetween('orders.created_at', [$last_month, $today])
            ->groupBy('products.category')
            ->pluck('revenue', 'category')
            ->toArray();

        return [
            'sales' => $sales,
            'categories' => PRODUCT_CATEGORIES
        ];
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
     * Update user.
     */
    public function updateUser(Request $request, User $user)
    {   
        $validator = validator()->make($request->all(), [
            'first_name'    => 'required|regex:/^[\pL\s\-]+$/u|min:2|max:30',
            'last_name'     => 'required|regex:/^[\pL\s\-]+$/u|min:2|max:30',
            'phone_number'  => 'required|regex:/^(\+62)[0-9]{9,11}$/|unique:users,email,'.$user->id,
            'email'         => 'required|email|max:50|unique:users,email,'.$user->id,
            'role'          => 'required|in:User,Admin',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $user->update([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'phone_number'  => $request->phone_number,
            'email'         => $request->email,
            'role'          => $request->role,
        ]);

        return redirect()->back()->withSuccess('Update user is successful.');
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

        return redirect()->back()->withSuccess('Update user status is successful.');
    }

    /**
     * Add balance.
     */
    public function addBalance(Request $request, User $user)
    {   
        $validator = validator()->make($request->all(), [
            'amount'    => 'required|numeric|min:5000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $user_id   = $user->id;
        $balance   = $user->balance;
        $amount    = $request->amount;
        $current_amount = $balance + $amount;

        $user->update([
            'balance' => $current_amount
        ]);

        BalanceDetail::create([
            'user_id'           => $user_id,
            'order_id'          => 0,
            'amount'            => $amount,
            'previous_amount'   => $balance,
            'current_amount'    => $current_amount,
            'type'              => 'Admin'
        ]);

        return redirect()->back()->withSuccess('Add balance is successful.');
    }

    /**
     * Show the balance details dashboard.
     */
    public function getBalanceDetails(Request $request)
    {
        $email         = $request->email;
        $phone_number  = $request->phone_number;
        $type          = $request->type;
        $date          = $request->date;

        $balance_details = BalanceDetail::join('users', 'balance_details.user_id', '=', 'users.id')
            ->select('balance_details.*')
            ->orderBy('created_at', 'desc');

        if ($email) {
            $balance_details->where('users.email', $email);
        }

        if ($phone_number) {
            $balance_details->where('users.phone_number', $phone_number);
        }

        if ($type) {
            $balance_details->where('balance_details.type', $type);
        }

        if ($date) {
            $start_date     = substr($date, 0, 10);
            $end_date       = substr($date, 13, 19);

            $balance_details->whereDate('balance_details.created_at', '>=', $start_date);
            $balance_details->whereDate('balance_details.created_at', '<=', $end_date);
        }

        $balance_details = $balance_details->paginate(10);
        $balance_details->withPath("/admin/balance-details?email=$email&phone_number=$phone_number&date=$date");

        return view('admin/balance_detail', [
            'page_title'      => 'Balance Details',
            'balance_details' => $balance_details,
            'types'     => BALANCE_TYPES
        ]);
    }


    /**
     * Show the orders dashboard.
     */
    public function getOrders(Request $request)
    {
        $reference_id   = $request->reference_id;
        $email          = $request->email;
        $status         = $request->status;
        $product_category = $request->product_category;
        $date           = $request->date;

        $orders = Order::join('users', 'orders.user_id', '=', 'users.id')
            ->join('products', 'orders.product_code', '=', 'products.code')
            ->select('orders.*', 'users.email')
            ->where('orders.status', '!=', 0)
            ->orderBy('orders.created_at', 'desc');
            
        if ($reference_id) {
            $orders->where('orders.reference_id', $reference_id);
        }

        if ($email) {
            $orders->where('users.email', $email);
        }

        if ($status) {
            if ($status != 'All') {
                $orders->where('orders.status', $status);
            }
        } else {
            $orders->where('orders.status', ORDER_STATUSES['pending_verification']);
        }

        if ($product_category) {
            $orders->where('products.category', $product_category);
        }

        if ($date) {
            $start_date     = substr($date, 0, 10);
            $end_date       = substr($date, 13, 19);

            $orders->whereDate('orders.created_at', '>=', $start_date);
            $orders->whereDate('orders.created_at', '<=', $end_date);
        }

        $orders = $orders->paginate(10);
        $orders->withPath("/admin/orders?reference_id=$reference_id&email=$email&status=$status&date=$date");

        return view('admin/order', [
            'page_title' => 'Orders',
            'orders'     => $orders,
            'statuses'   => ORDER_STATUSES,
            'product_categories' => PRODUCT_CATEGORIES
        ]);
    }

    /**
     * Verify the order (for Bank Transfer).
     */
    public function verifyOrder(Request $request) 
    {
        $order_id   = $request->order_id;

        $order  = Order::where('id', $order_id)
            ->where('status', ORDER_STATUSES['pending_verification'])
            ->where('payment_method', 'Bank Transfer')
            ->first();

        if (!$order) {
            return redirect('admin/orders')->withErrors('Order is not found or has been confirmed.');
        }

        $user_id    = $order->user_id;
        $product_code = $order->product_code;

        $user   = User::find($user_id);
        $fcm_token = $user->fcm_token_android;

        $product    = Product::find($product_code);
        $product_category    = $product->category;

        // if ($product_category == 'Saldo') {
            $balance        = $user->balance;
            $topup_amount   = $order->order_amount;
            $current_amount = $balance + $topup_amount;

            // update user balance
            $user->balance = $current_amount;
            $user->save();

            // create deposit detail
            BalanceDetail::create([
                'user_id'           => $user_id,
                'order_id'          => $order_id,
                'amount'            => $topup_amount,
                'previous_amount'   => $balance,
                'current_amount'    => $current_amount,
                'type'              => 'Top up'
            ]);

            $order->status = ORDER_STATUSES['completed'];
            $order->save();

        sendPushNotification([
            'fcm_token' => $fcm_token,
            'title'     => 'Berhasil!',
            'body'      => "Top up sudah berhasil dilakukan.",
            'order_id'  => $order_id,
            'click_action' => 'DetailTransaction'
        ]);

        // } else {
        //     $order->status = ORDER_STATUSES['pending_processing'];
        //     $order->save();

        //     $dji_product_id = $product->dji_product_id;
        //     $reference_id   = $order->reference_id;
        //     $customer_number = $order->customer_number;
        //     $product_price  = $order->product_price;
        //     $admin_fee      = $order->admin_fee;
        //     $order_amount   = $order->order_amount;

        //     $djiClient = new \App\Classes\DJIClient;
        //     $result = $djiClient->payment([
        //         'dji_product_id'    => $dji_product_id,
        //         'customer_number'   => $customer_number,
        //         'reference_id'      => $reference_id, 
        //         'product_price'     => $product_price,
        //         'admin_fee'         => $admin_fee,
        //         'order_amount'      => $order_amount    
        //     ]);

        //     if (isset($result->rc) && $result->rc != '00') {
        //         return redirect()->back()->withErrors([$result->rc . ': ' . trim($result->description)]);
        //     }

        //     $order->status = ORDER_STATUSES['completed'];
        //     $order->save();
        // }

        return redirect()->back()->withSuccess('Verify order is successful.');
    }

    /**
     * Cancel the order.
     */
    public function cancelOrder(Request $request) 
    {
        $validator = validator()->make($request->all(), [
            'cancellation_reason' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect('admin/orders')->withErrors($validator->errors()->first());
        }

        $order_id   = $request->order_id;
        $cancellation_reason = $request->cancellation_reason;

        $order  = Order::where('id', $order_id)
            ->where('status', '!=', ORDER_STATUSES['completed'])
            ->first();

        if (!$order) {
            return redirect('admin/orders')->withErrors('Order is not found or has been confirmed.');
        }

        $order->cancellation_reason = $cancellation_reason;
        $order->status = ORDER_STATUSES['cancelled'];
        $order->save();

        return redirect()->back()->withSuccess('Cancel order is successful.');
    }

    /**
     * Refund the order.
     */
    public function refundOrder(Request $request) 
    {
        $validator = validator()->make($request->all(), [
            'refund_amount' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect('admin/orders')->withErrors($validator->errors()->first());
        }

        $order_id   = $request->order_id;
        $refund_amount = $request->refund_amount;

        $order  = Order::where('id', $order_id)
            ->where('status', '!=', ORDER_STATUSES['completed'])
            ->first();

        if (!$order) {
            return redirect('admin/orders')->withErrors('Order is not found or has been confirmed.');
        }

        $user_id    = $order->user_id;
        $user       = User::find($user_id);

        $balance        = $user->balance;
        $current_amount = $balance + $refund_amount;

        $user->balance = $current_amount;
        $user->save();

        BalanceDetail::create([
            'user_id'           => $user_id,
            'order_id'          => $order_id,
            'amount'            => $refund_amount,
            'previous_amount'   => $balance,
            'current_amount'    => $current_amount,
            'type'              => 'Refund'
        ]);

        Refund::create([
            'order_id'   => $order_id,
            'amount'     => $refund_amount,
            'status'     => 1
        ]);

        return redirect()->back()->withSuccess('Refund order is successful.');
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
            'products'      => $products,
            'categories'    => PRODUCT_CATEGORIES
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

        return redirect()->back()->withSuccess('Update product status is successful.');
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

        return redirect()->back()->withSuccess('Update recipient bank status is successful.');
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

        return redirect()->back()->withSuccess('Update sender bank status is successful.');
    }
}
