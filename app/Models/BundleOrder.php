<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundleOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_id',
        'recipient_number',
        'mpesa_number',
        'amount',
        'checkout_request_id',
        'mpesa_receipt',
        'status',
    ];

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }
}
