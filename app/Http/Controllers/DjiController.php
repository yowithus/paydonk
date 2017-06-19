<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DjiController extends Controller
{
    private $username;
    private $password;
    private $account_id;
    private $merchant_id;
    private $base_uri;

	public function __construct()
    {
        // $this->middleware('guest');
        $this->username     = ENV('DJI_USERNAME');
        $this->password     = ENV('DJI_PASSWORD');
        $this->account_id   = ENV('DJI_ACCOUNT_ID');
        $this->merchant_id  = ENV('DJI_MERCHANT_ID');
        $this->base_uri     = ENV('DJI_BASE_URI');
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
        $base_uri       = $this->base_uri;
        $request_uri    = '/auth/Sign-On';
        $request_method = 'POST';

        $client     = new \GuzzleHttp\Client(['base_uri' => $base_uri, 'verify' => false, 'exceptions' => false]);
        $res        = $client->post($request_uri);

        $digest = $res->getHeaderLine('WWW-Authenticate');
        if (strpos($digest,'Digest') === 0) {
            $digest = substr($digest, 7);
        }

        $data       = $this->parseHttpDigest($digest);
        $username   = $this->username;
        $password   = $this->password;
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

    public function signOn(Request $request)
    {
        $base_uri       = $this->base_uri;
        $request_uri    = '/auth/Sign-On';
        $authorization  = $this->getAuthorization();

        $client = new \GuzzleHttp\Client(['base_uri' => $base_uri, 'verify' => false, 'exceptions' => false]);
        $response   = $client->post($request_uri, [
            'headers'   => ['Authorization' => $authorization],
            'json' => [
               'mitra'  	  => 'DJI', 
               'accountID' 	  => $this->account_id,
               'merchantID'   => $this->merchant_id,
               'merchantName' => 'allPay',
               'counterID' 	  => '1'
            ],
        ]);

        $body   = $response->getBody()->read(1024);
        $result = json_decode((string)$body);

        return response()->json($result);
    }

    public function inquiry(Request $request)
    {
        $base_uri       = $this->base_uri;
        $request_uri    = '/Services/Inquiry';
        $authorization  = $this->getAuthorization();

        // get session id from sign on
        $result = $this->signOn($request)->getData();
        if (isset($result->rc) && $result->rc != '00') {
            return response()->json($result);
        }

        $session_id = $result->SessionID;

        $client = new \GuzzleHttp\Client(['base_uri' => $base_uri, 'verify' => false, 'exceptions' => false]);
        $response   = $client->post($request_uri, [
            'headers'   => ['Authorization' => $authorization],
            'json' => [
               'sessionID'      => $session_id, 
               'merchantID'     => $this->merchant_id,
               'productID'      => $request->dji_product_id,
               'customerID'     => $request->customer_number,
               'referenceID'    => $request->reference_id, 
            ],
        ]);

        $body   = $response->getBody()->read(1024);
        $result = json_decode((string)$body);

        return response()->json($result);
    }

    public function payment(Request $request)
    {
        $base_uri       = $this->base_uri;
        $request_uri    = '/Services/Payment';
        $authorization  = $this->getAuthorization();

        $result = $this->inquiry($request)->getData();
        if (isset($result->rc) && $result->rc != '00') {
            return response()->json($result);
        }

        $session_id = $result->sessionID;

        $client = new \GuzzleHttp\Client(['base_uri' => $base_uri, 'verify' => false, 'exceptions' => false]);
        $response   = $client->post($request_uri, [
            'headers'   => ['Authorization' => $authorization],
            'json' => [
               'sessionID'      => $session_id, 
               'merchantID'     => $this->merchant_id,
               'productID'      => $request->dji_product_id,
               'customerID'     => $request->customer_number,
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
}