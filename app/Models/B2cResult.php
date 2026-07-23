<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2cResult extends Model
{
    protected $fillable = [
        'originator_conversation_id',
        'conversation_id',
        'transaction_id',
        'result_code',
        'result_desc',
        'amount',
        'receipt',
        'receiver_name',
        'utility_balance',
        'completed_at',
        'raw_payload',
    ];
}
