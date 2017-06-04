<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\TopUpOrder;
use App\TopUpBankTransfer;
use App\DepositDetail;

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
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    /**
     * Show the user dashboard.
     *
     * @return \Illuminate\Http\Response
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
     *
     * @return \Illuminate\Http\Response
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
     *
     * @return \Illuminate\Http\Response
     */
    public function getTopUpOrders()
    {
        return view('admin/order_topup', [
            'page_title'    => 'Top up',
            'topup_orders'  => TopUpOrder::all()
        ]);
    }


    public function verifyTopUpOrder(Request $request) 
    {
        $validator = validator()->make($request->all(), [
            'user_id'        => 'required',
            'topup_order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('admin/topup-orders')->withErrors($validator);
        }

        $user        = User::find($request->user_id);
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
            'user_id'           => $request->topup_order_id,
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
