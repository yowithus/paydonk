<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use App\PhoneVerification;
use Twilio;


class UserController extends Controller
{
	public function __construct()
    {
       // $this->middleware('guest');
       $this->middleware('jwt.auth', ['except' => ['login', 'register']]);
    }  

    public function show()
    {
        $user = JWTAuth::parseToken()->authenticate();

        return response()->json(compact('user'));
    }

    public function register(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'phone_number'  => 'required|max:80|unique:users',
            'password'      => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $user = User::create([
            'phone_number'      => $request->phone_number,
            'password'          => bcrypt($request->password)
        ]);

        $verification_code = rand(1000, 9999);

        PhoneVerification::create([
            'user_id'           => $user->id,
            'phone_number'      => $request->phone_number,
            'verification_code' => $verification_code,
        ]);

        Twilio::message($request->phone_number, $verification_code);

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

        return response()->json([
            'status'    => 1,
            'message'   => 'Login successful',
            'token'     => $token
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
        $user = JWTAuth::parseToken()->authenticate();

        $verification_code = rand(1000, 9999);

        PhoneVerification::create([
            'user_id'           => $user->id,
            'phone_number'      => $user->phone_number,
            'verification_code' => $verification_code,
        ]);

        Twilio::message($user->phone_number, $verification_code);

        return response()->json([
            'status'    => 1,
            'message'   => 'Send verification code successful'
        ]);
    }

    public function verify(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        
        if ($user->phone_verification()->verification_code != $request->verification_code) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Verify failed'
            ]);
        }

        $user->is_phone_verified = true;
        $user->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Verify successful'
        ]);
    }

    public function saveUserInfo(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'first_name' => 'required|regex:/^[\pL\s\-]+$/u|min:2|max:30',
            'last_name'  => 'required|regex:/^[\pL\s\-]+$/u|min:2|max:30',
            'email'      => 'required|email|max:80|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $user->first_name   = $request->first_name;
        $user->last_name    = $request->last_name;
        $user->email        = $request->email;
        $user->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Successfully save user info'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'password'  => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => $validator->errors()->first()
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'status'    => 1,
            'message'   => 'Successfully reset password'
        ]);
    }
}
