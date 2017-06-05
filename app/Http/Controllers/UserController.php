<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use Twilio;
use DB;


class UserController extends Controller
{
	public function __construct()
    {
       // $this->middleware('guest');
       $this->middleware('jwt.auth', ['except' => ['login', 'register', 'sendVerificationCode', 'verify', 'resetPassword', 'djiLogin', 'djiRegister', 'djiInquiry', 'djiPayment']]);
    }  

    public function show()
    {
        $user = JWTAuth::parseToken()->authenticate();

        return response()->json(compact('user'));
    }

    private function parseHttpDigest($txt)
    {
        $keys_arr = array();
        $values_arr = array();
        $cindex = 0;
        $parts = explode(',', $txt);

        foreach($parts as $p) {
            $p = trim($p);
            $kvpair = explode('=', $p);
            $kvpair[1] = str_replace("\"", "", $kvpair[1]);
            $keys_arr[$cindex] = $kvpair[0];
            $values_arr[$cindex] = $kvpair[1];
            $cindex++;
        }
      
        $ret_arr = array_combine($keys_arr, $values_arr);

        return $ret_arr;  
    }

    private function getAuthorization()
    {
        $base_uri       = "https://182.253.236.154:32146";
        $request_uri    = '/auth/Login';
        $request_method = 'POST';

        $client     = new \GuzzleHttp\Client(['base_uri' => $base_uri, 'verify' => false, 'exceptions' => false]);
        $res        = $client->post($request_uri);

        $digest = $res->getHeaderLine('WWW-Authenticate');
        if (strpos($digest,'Digest') === 0) {
            $digest = substr($digest, 7);
        }

        $data       = $this->parseHttpDigest($digest);
        $username   = 'dji';
        $password   = 'abcde';
        $realm      = $data['realm'];
        $qop        = $data['qop'];
        $nonce      = $data['nonce'];
        $opaque     = $data['opaque'];
        $nc         = '00000001';
        $cnonce     = '098f6bcd4621d373cade4e832627b4f6';

        $A1         = md5("$username:$realm:$password");
        $A2         = md5("$request_method:$request_uri");
        $response   = md5("$A1:$nonce:$nc:$cnonce:$qop:$A2");

        $authorization = "Digest username=\"$username\", realm=\"$realm\", nonce=\"$nonce\", uri=\"$request_uri\", qop=\"$qop\", nc=\"$nc\", cnonce=\"$cnonce\", response=\"$response\", opaque=\"$opaque\"";

        return $authorization;
    }

    public function djiLogin(Request $request)
    {
        $base_uri       = "https://182.253.236.154:32146";
        $request_uri    = '/auth/Login';
        $authorization  = $this->getAuthorization();

        $client = new \GuzzleHttp\Client(['base_uri' => $base_uri, 'verify' => false, 'exceptions' => false]);
        $response   = $client->post($request_uri, [
            'headers'   => ['Authorization' => $authorization],
            'json' => [
               'accountID'  => '081932058111', 
               'hardwareID' => 'tes123',
               'password'   => md5('958939')
            ],
        ]);

        $body   = $response->getBody()->read(1024);
        $result = json_decode((string)$body);

        return response()->json($result);
    }

    public function djiRegister(Request $request)
    {
        $base_uri       = "https://182.253.236.154:32146";
        $request_uri    = '/Services/Registrasi-Merchant';
        $authorization  = $this->getAuthorization();

        $client = new \GuzzleHttp\Client(['base_uri' => $base_uri, 'verify' => false, 'exceptions' => false]);
        $response   = $client->post($request_uri, [
            'headers'   => ['Authorization' => $authorization],
            'json' => [
               'msisdn' => '081932058111', 
               'email'  => 'yonatan.nugraha@hotmail.com', 
               'name'   => 'Yonatan Nugraha',
               'upline' => '',
               'serial' => 'tes123'
            ],
        ]);

        $body   = $response->getBody()->read(1024);
        $result = json_decode((string)$body);

        return response()->json($result);
    }

    public function djiInquiry(Request $request)
    {
        $base_uri       = "https://182.253.236.154:32146";
        $request_uri    = '/Services/Inquiry';
        $authorization  = $this->getAuthorization();

        $client = new \GuzzleHttp\Client(['base_uri' => $base_uri, 'verify' => false, 'exceptions' => false]);
        $response   = $client->post($request_uri, [
            'headers'   => ['Authorization' => $authorization],
            'json' => [
               'sessionID'      => $request->dji_session_id, 
               'merchantID'     => 'DRS111112',
               'productID'      => $request->dji_product_id,
               'customerID'     => $request->customer_number,
               'accountID'      => '081932058111',
               'counterID'      => '1',
               'referenceID'    => $request->reference_id, 
            ],
        ]);

        $body   = $response->getBody()->read(1024);
        $result = json_decode((string)$body);

        return response()->json($result);
    }

    public function djiPayment(Request $request)
    {
        $base_uri       = "https://182.253.236.154:32146";
        $request_uri    = '/Services/Payment';
        $authorization  = $this->getAuthorization();

        $client = new \GuzzleHttp\Client(['base_uri' => $base_uri, 'verify' => false, 'exceptions' => false]);
        $response   = $client->post($request_uri, [
            'headers'   => ['Authorization' => $authorization],
            'json' => [
               'sessionID'      => $request->dji_session_id, 
               'merchantID'     => 'DRS111112',
               'productID'      => $request->dji_product_id,
               'accountID'      => '081932058111',
               'counterID'      => '1',
               'customerID'     => $request->customer_number,
               'pin'            => md5('879123'),
               'referenceID'    => $request->reference_id, 
               'tagihan'        => $request->tagihan,
               'admin'          => $request->admin,
               'total'          => $request->total
            ],
        ]);

        $body   = $response->getBody()->read(1024);
        $result = json_decode((string)$body);

        return response()->json($result);
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
