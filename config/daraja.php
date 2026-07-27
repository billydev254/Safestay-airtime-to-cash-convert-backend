<?php

return [
    'base_url' => env('DARAJA_BASE_URL', 'https://sandbox.safaricom.co.ke'),

    // Till (Buy Goods) — bundle purchases: STK push + C2B collection.
    // store_number is NOT the till shortcode — Safaricom provisions Till
    // STK push with a separate "Store Number" (get it by dialing *234#
    // from the till's registered line). Using the till shortcode for PartyB
    // fails with ResultCode 2002 "Agent number and Store number entered do
    // not match" — confirmed via direct testing against production.
    'till' => [
        'shortcode' => env('DARAJA_TILL_SHORTCODE'),
        'store_number' => env('DARAJA_TILL_STORE_NUMBER'),
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
