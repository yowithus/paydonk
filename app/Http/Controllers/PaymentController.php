<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class PaymentController extends Controller
{
    public function __construct()
    {
    }  

    public function getRecipientBanks()
    {
    	$recipient_banks = DB::table('recipient_banks')
    		->where('status', 1)
    		->get();

    	return response()->json(compact('recipient_banks'));
    }

    public function getSenderBanks()
    {
    	$sender_banks = DB::table('sender_banks')
    		->where('status', 1)
    		->get();

    	return response()->json(compact('sender_banks'));
    }

    public function topup() {
    	
    }
}
