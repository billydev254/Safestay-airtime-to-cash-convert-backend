<?php

return [
    'base_url' => env('DARAJA_BASE_URL', 'https://sandbox.safaricom.co.ke'),

    // Till (Buy Goods) — bundle purchases: STK push + C2B collection
    'till' => [
        'shortcode' => env('DARAJA_TILL_SHORTCODE'),
        'consumer_key' => env('DARAJA_TILL_CONSUMER_KEY'),
        'consumer_secret' => env('DARAJA_TILL_CONSUMER_SECRET'),
        'passkey' => env('DARAJA_TILL_PASSKEY'),
    ],

    // Paybill — airtime-to-cash / Bonga B2C payouts (B2C requires an org/paybill shortcode)
    'paybill' => [
        'shortcode' => env('DARAJA_PAYBILL_SHORTCODE'),
        'consumer_key' => env('DARAJA_PAYBILL_CONSUMER_KEY'),
        'consumer_secret' => env('DARAJA_PAYBILL_CONSUMER_SECRET'),
        'initiator_name' => env('DARAJA_PAYBILL_INITIATOR_NAME'),
        'security_credential' => env('DARAJA_PAYBILL_SECURITY_CREDENTIAL'),
    ],
];
