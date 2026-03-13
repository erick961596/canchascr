<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    'onvopay' => [
        'public'         => env('ONVO_PUBLIC_KEY'),
        'secret'         => env('ONVO_SECRET_KEY'),
        'webhook_secret' => env('ONVO_WEBHOOK_SECRET'),
    ],

    'twilio' => [
        'sid'            => env('TWILIO_SID'),
        'token'          => env('TWILIO_TOKEN'),
        'whatsapp_from'  => env('TWILIO_WHATSAPP_FROM'),
    ],

];
