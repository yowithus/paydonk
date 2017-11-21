<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use App\Product;
use App\CreditCardToken;
use Twilio;
use DB;
use Mail;
use App\Mail\Welcome;
use Carbon\Carbon;

class UserController extends Controller
{
	public function __construct()
    {
       // $this->middleware('guest');
       $this->middleware('jwt.auth', ['except' => [
            'login', 
            'register', 
            'sendVerificationCode', 
            'verify', 
            'resetPassword'
        ]]);
    }  

    public function getUser()
    {
        $user = JWTAuth::parseToken()->authenticate();

        // $asd = sendPushNotification([
        //     'fcm_token' => '',
        //     'title' => 'asd',
        //     'body' => 'asd',
        //     'type' => 'asd'
        // ]);

        // Mail::to($user->email)->queue(new Welcome($user));

        return response()->json([
            'status'    => 1,
            'message'   => 'Get user successful',
            'user'      => $user
        ]);
    }

    public function register(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'first_name'    => 'required|regex:/^[\pL\s\-]+$/u|min:2|max:30',
            'last_name'     => 'required|regex:/^[\pL\s\-]+$/u|min:2|max:30',
            'phone_number'  => 'required|regex:/^(\+62)[0-9]{9,11}$/|unique:users',
            'email'         => 'required|email|max:50|unique:users',
            'password'      => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $user = User::create([
            'first_name'        => $request->first_name,
            'last_name'         => $request->last_name,
            'phone_number'      => $request->phone_number,
            'email'             => $request->email,
            'password'          => bcrypt($request->password)
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status'    => 1,
            'message'   => 'Register successful',
            'token'     => $token
        ]);
    }

    public function login(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'phone_number'  => 'required|regex:/^(\+62)[0-9]{9,11}$/',
            'password'      => 'required|string|min:6',
            'device_type'   => 'required|in:Android,iOS',
            'fcm_token_android' => 'required_if:device_type,==,Android',
            'fcm_token_ios' => 'required_if:device_type,==,iOS',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $credentials = $request->only('phone_number', 'password');
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Nomor telepon atau password salah.'
            ]);
        }

        $user = auth()->User();

        // update fcm token
        $device_type        = $request->device_type;
        $fcm_token_android  = $request->fcm_token_android;
        $fcm_token_ios      = $request->fcm_token_ios;

        if ($device_type == 'Android') {
            $user->fcm_token_android = $fcm_token_android;
        } else if ($device_type == 'iOS') {
            $user->fcm_token_ios = $fcm_token_ios;
        }

        $user->updated_at = Carbon::now();
        $user->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Login successful',
            'token'     => $token,
            'user'      => $user
        ]);
    }

    public function logout(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'device_type'   => 'required|in:Android,iOS',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();

        // empty fcm token
        $device_type        = $request->device_type;

        if ($device_type == 'Android') {
            $user->fcm_token_android = null;
        } else if ($device_type == 'iOS') {
            $user->fcm_token_ios = null;
        }

        $user->updated_at = Carbon::now();
        $user->save();

        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'status'    => 1,
            'message'   => 'Logout successful',
        ]);
    }

    public function sendVerificationCode(Request $request)
    {
        if ($request->is_register) {
            $validator = validator()->make($request->all(), [
                'phone_number'  => 'required|regex:/^(\+62)[0-9]{9,11}$/|unique:users',
            ]);  
        } else {
            $validator = validator()->make($request->all(), [
                'phone_number'  => 'required|regex:/^(\+62)[0-9]{9,11}$/'
            ]); 
        }

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $phone_number      = $request->phone_number;
        $verification_code = rand(1000, 9999);

        DB::table('phone_verifications')->insert([
            'phone_number'      => $phone_number,
            'verification_code' => $verification_code,
        ]);

        Twilio::message($phone_number, $verification_code);

        return response()->json([
            'status'    => 1,
            'message'   => 'Send verification code successful'
        ]);
    }

    public function verify(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'phone_number'      => 'required|regex:/^(\+62)[0-9]{9,11}$/',
            'verification_code' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $phone_number       = $request->phone_number;
        $verification_code  = $request->verification_code;

        $phone_verification = DB::table('phone_verifications')
            ->where('phone_number', $phone_number)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($verification_code != $phone_verification->verification_code) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Kode verifikasi yang ada masukan salah.'
            ]);
        }

        return response()->json([
            'status'    => 1,
            'message'   => 'Verify successful'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'phone_number'  => 'required|regex:/^(\+62)[0-9]{9,11}$/',
            'password'      => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $user = User::where('phone_number', $request->phone_number)
            ->first();

        if (!$user) {
            return response()->json([
                'status'    => 0,
                'message'   => 'User tidak ditemukan.'
            ]);
        }

        $user->password = bcrypt($request->password);
        $user->updated_at = Carbon::now();
        $user->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Reset password successful'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = validator()->make($request->all(), [
            'first_name'    => 'required|regex:/^[\pL\s\-]+$/u|min:2|max:30',
            'last_name'     => 'required|regex:/^[\pL\s\-]+$/u|min:2|max:30',
            'email'         => 'required|email|max:50|unique:users,email,'.$user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $user->first_name   = $request->first_name;
        $user->last_name    = $request->last_name;
        $user->email        = $request->email;
        $user->updated_at   = Carbon::now();
        $user->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Update profile successful'
        ]);
    }

    public function updateProfilePicture(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;

        $validator = validator()->make($request->all(), [
            'image' => 'required|mimes:jpeg,jpg,png,bmp|max:2000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $image_name = md5("user-$user_id") . '.jpg';

        $request->image->move(public_path('images/users'), $image_name);

        $user->image_name = $image_name;
        $user->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Update profile picture successful'
        ]);
    }

    public function updateFCMToken(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'device_type'       => 'required|in:Android,iOS',
            'fcm_token_android' => 'required_if:device_type,==,Android',
            'fcm_token_ios'     => 'required_if:device_type,==,iOS',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();

        // update fcm token
        $device_type        = $request->device_type;
        $fcm_token_android  = $request->fcm_token_android;
        $fcm_token_ios      = $request->fcm_token_ios;

        if ($device_type == 'Android') {
            $user->fcm_token_android = $fcm_token_android;
        } else if ($device_type == 'iOS') {
            $user->fcm_token_ios = $fcm_token_ios;
        }

        $user->updated_at = Carbon::now();
        $user->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Update fcm token successful',
        ]);
    }

    public function storeCreditCardToken(Request $request) 
    {
        $user = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;

        $validator = validator()->make($request->all(), [
            'token_id'       => 'required',
            'masked_card_number' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $token_id = $request->token_id;
        $masked_card_number = $request->masked_card_number;
        
        CreditCardToken::updateOrCreate([
            'user_id' => $user_id,
            'masked_card_number' => $masked_card_number
        ], [
            'token_id'      => $token_id,
            'masked_card_number' => $masked_card_number
        ]);

        return response()->json([
            'status'    => 1,
            'message'   => 'Store credit card token successful'
        ]);
    }

    public function getCreditCardTokens() 
    {
        $user = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'status'    => 1,
            'message'   => 'Get credit card tokens successful',
            'credit_card_tokens'  => $user->credit_card_tokens
        ]);
    }

    public function getBalanceDetails() 
    {
        $user = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'status'    => 1,
            'message'   => 'Get balance details successful',
            'balance_details'  => $user->balance_details()->with('order.product')->get()
        ]);
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

    public function getOrderDetails($order_id) 
    {
        $user = JWTAuth::parseToken()->authenticate();

        $order = $user->orders->find($order_id);

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
}
