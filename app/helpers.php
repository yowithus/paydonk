<?php

// global variables
define('PRODUCT_CATEGORIES', [
  'Saldo',
  'PLN',
  'PDAM',
  'TV Kabel',
  'Pulsa',
  'Paket Data',
  'Telkom',
  'Angsuran Kredit',
  'BPJS',
  'Voucher Game',
  'Streaming',
]);

define('ORDER_STATUSES', [
  'voided'              => 0,
  'pending_created'     => 1,
  'pending_selection'   => 2,
  'pending_payment'     => 3,
  'pending_verification'=> 4, // for bank transfer
  'pending_processing'  => 5,
  'completed'           => 6,
  'cancelled'           => 7
]);

define('BALANCE_TYPES', [
  'Top Up',
  'Payment',
  'Refund',
  'Admin'
]);

define('OPERATOR_PREFIXES', [
  // Indosat
  '0814' => 'Indosat',
  '0815' => 'Indosat',
  '0816' => 'Indosat',
  '0855' => 'Indosat',
  '0856' => 'Indosat',
  '0857' => 'Indosat',
  '0858' => 'Indosat',
  '0817' => 'Indosat',
  '0817' => 'Indosat',
  '0817' => 'Indosat',

  // Telkomsel
  '0811' => 'Telkomsel', // Halo
  '0812' => 'Telkomsel',
  '0813' => 'Telkomsel',
  '0821' => 'Telkomsel',
  '0822' => 'Telkomsel',
  '0823' => 'Telkomsel',
  '0851' => 'Telkomsel',
  '0852' => 'Telkomsel',
  '0853' => 'Telkomsel',

  // XL
  '0817' => 'XL',
  '0818' => 'XL',
  '0819' => 'XL',
  '0859' => 'XL',
  '0877' => 'XL',
  '0878' => 'XL',

  // Axis
  '0831' => 'Axis',
  '0832' => 'Axis',
  '0833' => 'Axis',
  '0838' => 'Axis',

  // Smartfren
  '0881' => 'Smartfren',
  '0882' => 'Smartfren',
  '0883' => 'Smartfren',
  '0884' => 'Smartfren',
  '0885' => 'Smartfren',
  '0886' => 'Smartfren',
  '0887' => 'Smartfren',
  '0888' => 'Smartfren',
  '0889' => 'Smartfren',

  // 3
  '0895' => '3',
  '0896' => '3',
  '0897' => '3',
  '0898' => '3',
  '0899' => '3',

  // Bolt
  '0998' => 'Bolt',
  '0999' => 'Bolt',
]);


// global functions
function sendPushNotification($data)
{
	$base_uri       = 'https://fcm.googleapis.com';
    $request_uri    = '/fcm/send';
    $authorization  = 'key=' . ENV('FCM_SERVER_KEY');

    $fcm_token    = $data['fcm_token'];
    $title        = $data['title'];
    $body         = $data['body'];
    $order_id     = $data['order_id'];
    $click_action = $data['click_action']; 
    // $product_code = $data['product_code'];

    $client = new \GuzzleHttp\Client(['base_uri' => $base_uri, 'verify' => false, 'exceptions' => false]);
    $response   = $client->post($request_uri, [
        'headers'   => ['Authorization' => $authorization],
        'json' => [
        	'to' => $fcm_token,
           	'notification' => [
           		'title' => $title,
           		'body' 	=> $body,
              'sound' => 'default'
           	], 
           	'data' 	=> [
           		'id' => $order_id,
              'click_action' => $click_action, //DetailTransaction, ListrikPLN, AirPDAM, BPJS, PaketData, PulsaPrabayar, Pascabayar
              //optional 'product_code' => 
           	],
        ],
    ]);

    $body   = $response->getBody()->read(1024);
    $result = json_decode((string)$body);

    return $result;
}

?>