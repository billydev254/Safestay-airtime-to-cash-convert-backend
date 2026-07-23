<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class C2bValidationLog extends Model
{
    protected $table = 'c2b_validation_log';

    protected $fillable = [
        'transaction_id',
        'msisdn',
        'amount',
        'decision',
        'raw_payload',
    ];
}
