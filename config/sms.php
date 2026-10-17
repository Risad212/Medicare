<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "log", "d7", "bulksmsbd", "twilio".
    | The "log" driver records messages without sending (development default).
    |
    */

    'driver' => env('SMS_DRIVER', 'log'),

    'from' => env('SMS_FROM'),

    'providers' => [

        'd7' => [
            'token' => env('D7_API_TOKEN'),
            'originator' => env('D7_ORIGINATOR'),
            'base_url' => env('D7_BASE_URL', 'https://api.d7networks.com'),
        ],

        'bulksmsbd' => [
            'api_key' => env('BULKSMSBD_API_KEY'),
            'senderid' => env('BULKSMSBD_SENDERID'),
            'base_url' => env('BULKSMSBD_BASE_URL', 'https://bulksmsbd.net/api'),
        ],

        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],

    ],

];
