<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivingNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'network',
        'msisdn',
        'active',
        'daily_limit',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
