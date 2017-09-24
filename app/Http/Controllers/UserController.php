<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
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

    public function show()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $asd = sendPushNotification([
            'fcm_token' => '',
            'title' => 'asd',
            'body' => 'asd',
            'type' => 'asd'
        ]);

        dd($asd);

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
            'password'          => bcrypt($request->password),
            'deposit'           => 0,
            'status'            => 1,
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
            'message'   => 'Successfully reset password'
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
}
