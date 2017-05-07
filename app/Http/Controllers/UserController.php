<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
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
            'phone'     => 'required|max:80|unique:users',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ]);
        }

        $verification_code = rand(1000, 9999);

        $user = User::create([
            'phone'         => $request->phone,
            'password'      => bcrypt($request->password),
            'verification_code' => $verification_code,
        ]);

        Twilio::message($request->phone, $verification_code);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success'   => 'Register successful',
            'token'     => $token
        ]);
    }

    public function login(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'phone'     => 'required|max:80',
            'password'  => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ]);
        }

        $credentials = $request->only('phone', 'password');
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Incorrect phone number or password.']);
        }

        return response()->json([
            'success'   => 'Login successful',
            'token'     => $token
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'success'   => 'Logout successful',
        ]);
    }

    public function sendVerificationCode(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $phone = $user->phone;
        $verification_code = rand(1000, 9999);

        $user->verification_code = $verification_code;
        $user->save();

        Twilio::message($phone, $verification_code);

        return response()->json([
            'success'   => 'Send verification code successful'
        ]);
    }

    public function verify(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        
        if ($user->verification_code != $request->verification_code) {
            return response()->json([
                'error'   => 'Verify failed'
            ]);
        }

        $user->is_verified = true;
        $user->save();

        return response()->json([
            'success'   => 'Verify successful'
        ]);
    }

    public function saveName(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'first_name' => 'required|regex:/^[\pL\s\-]+$/u|min:2|max:30',
            'last_name'  => 'required|regex:/^[\pL\s\-]+$/u|min:2|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->save();

        return response()->json([
            'success'   => 'Successfully save name'
        ]);
    }

    public function saveEmail(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'email' => 'required|email|max:80|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $user->email = $request->email;
        $user->save();

        return response()->json([
            'success'   => 'Successfully save email'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'password'  => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'success'   => 'Successfully reset password'
        ]);
    }
}
