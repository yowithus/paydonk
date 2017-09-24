<?php

function sendPushNotification($data)
{
	$base_uri       = 'https://fcm.googleapis.com/fcm/send';
    $request_uri    = '';
    $authorization  = 'key=adasd';

    $fcm_token 	= $data->fcm_token;
    $title 		= $data->title;
    $body 		= $data->body;
    $type 		= $data->type;

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

    return $response;
}

?>