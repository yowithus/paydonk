<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;


class AuthController extends Controller
{
	public function __construct()
    {
       $this->middleware('guest');
    }  

    public function login(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'email'     => 'required|email|max:80',
            'password'  => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ]);
        }

        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Incorrect email address or password.'], 401);
        }

        return response()->json(compact('token'));
    }

    public function register(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'name'      => 'required|regex:/^[\pL\s\-]+$/u|min:2|max:30',
            'email'     => 'required|email|max:80|unique:users',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ]);
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('token'));
    }
}
