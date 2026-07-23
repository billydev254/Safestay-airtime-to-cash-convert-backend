<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'network',
        'sender_number',
        'mpesa_number',
        'amount_in',
        'cashback_pct',
        'amount_payout',
        'status',
        'originator_conversation_id',
        'mpesa_receipt',
        'payout_result_desc',
    ];
}
