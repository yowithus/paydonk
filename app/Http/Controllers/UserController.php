<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use Twilio;
use DB;
use Auth;


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
            'phone_number'  => 'required|max:80|unique:users',
            'email'         => 'required|email|max:80|unique:users',
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
            'phone_number'  => 'required|max:80',
            'password'      => 'required|string|min:6',
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
                'message'   => 'Incorrect phone number or password.'
            ]);
        }

        $user = Auth::User();
        // dd($user);
        dd('b');

        return response()->json([
            'status'    => 1,
            'message'   => 'Login successful',
            'token'     => $token,
            'user'      => $user
        ]);
    }

    public function logout()
    {
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
                'phone_number'  => 'required|max:80|unique:users'
            ]);  
        } else {
            $validator = validator()->make($request->all(), [
                'phone_number'  => 'required|max:80'
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
            'phone_number'      => 'required|max:80',
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
                'message'   => 'Verify failed'
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
            'phone_number'  => 'required|max:80',
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
                'message'   => 'User does not exist'
            ]);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Successfully reset password'
        ]);
    }
}
