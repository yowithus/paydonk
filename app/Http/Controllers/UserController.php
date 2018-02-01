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
        $jwt_token_old = $user->jwt_token;

        if ($jwt_token_old) {
            try {
                JWTAuth::invalidate($jwt_token_old);
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                // token is already invalid
            }
            
        }

        // update fcm token
        $device_type        = $request->device_type;
        $fcm_token_android  = $request->fcm_token_android;
        $fcm_token_ios      = $request->fcm_token_ios;

        if ($device_type == 'Android') {
            $user->fcm_token_android = $fcm_token_android;
        } else if ($device_type == 'iOS') {
            $user->fcm_token_ios = $fcm_token_ios;
        }

        $user->jwt_token = $token;
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
        $is_register = $request->is_register;

        if ($is_register) {
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

        if (!$is_register && User::where('phone_number', '=', $phone_number)->count() == 0) {
            return response()->json([
                'status'    => 0,
                'message'   => 'User with this phone number does not exist'
            ]);
        }

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

    public function verifyPassword(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'password'      => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();

        $credentials = $request->only('password');
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Password salah.'
            ]);
        }

        return response()->json([
            'status'    => 1,
            'message'   => 'Verify password successful'
        ]);
    }

    public function savePinPattern(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'pin_pattern'  => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $user->pin_pattern = $request->pin_pattern;
        $user->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Save pin pattern successful'
        ]);
    }

    public function verifyPinPattern(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'pin_pattern'  => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();
        if ($user->pin_pattern != $request->pin_pattern) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Pin salah.'
            ]);
        }

        return response()->json([
            'status'    => 1,
            'message'   => 'Verify pin pattern successful'
        ]);
    }


    public function storeCreditCardToken(Request $request) 
    {
        $user = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;

        $validator = validator()->make($request->all(), [
            'token_id'       => 'required',
            'masked_card_number' => 'required',
            'card_brand'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $token_id = $request->token_id;
        $masked_card_number = $request->masked_card_number;
        $card_brand = $request->card_brand;
        
        CreditCardToken::updateOrCreate([
            'user_id' => $user_id,
            'masked_card_number' => $masked_card_number
        ], [
            'token_id'      => $token_id,
            'masked_card_number' => $masked_card_number,
            'card_brand' => $card_brand
        ]);

        return response()->json([
            'status'    => 1,
            'message'   => 'Store credit card token successful'
        ]);
    }

    public function deleteCreditCardToken($id) 
    {
        $user = JWTAuth::parseToken()->authenticate();

        $credit_card_token = CreditCardToken::find($id);

        if ($credit_card_token) {
            $credit_card_token->delete();
        }

        return response()->json([
            'status'    => 1,
            'message'   => 'Delete credit card tokens successful'
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
}
