<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class C2bPayment extends Model
{
    protected $fillable = [
        'transaction_id',
        'transaction_type',
        'trans_time',
        'amount',
        'business_shortcode',
        'bill_ref_number',
        'invoice_number',
        'msisdn',
        'first_name',
        'middle_name',
        'last_name',
        'raw_payload',
    ];
}
