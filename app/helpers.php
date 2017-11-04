<?php

// global variables
define('ORDER_STATUSES', [
    1   => 'Menunggu Pembayaran',
    2   => 'Pilih Pembayaran',
    3   => 'Menunggu Pembayaran',
    4   => 'Memverifikasi Pembayaran',
    5   => 'Sedang Diproses',
    6   => 'Berhasil',
    7   => 'Dibatalkan'
]);

// global functions
function sendPushNotification($data)
{
	$base_uri       = 'https://fcm.googleapis.com';
    $request_uri    = '/fcm/send';
    $authorization  = 'key=' . ENV('FCM_SERVER_KEY');

    $fcm_token 	= $data['fcm_token'];
    $title 		= $data['title'];
    $body 		= $data['body'];
    $type 		= $data['type'];

    $client = new \GuzzleHttp\Client(['base_uri' => $base_uri, 'verify' => false, 'exceptions' => false]);
    $response   = $client->post($request_uri, [
        'headers'   => ['Authorization' => $authorization],
        'json' => [
        	'to' => $fcm_token,
           	'notification' => [
           		'title' => $title,
           		'body' 	=> $body
           	], 
           	'data' 	=> [
           		'type' => $type
           	],
        ],
    ]);

    $body   = $response->getBody()->read(1024);
    $result = json_decode((string)$body);

    return $result;
}

?>