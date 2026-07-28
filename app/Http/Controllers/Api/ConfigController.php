<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\ReceivingNumber;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

/**
 * Config-driven app: the Flutter app fetches cashback %, Bonga rate, the
 * bundle catalog, and active receiving numbers from here instead of having
 * any of it hardcoded, so the client can change prices/rates himself via
 * the admin dashboard without an app update.
 */
class ConfigController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'cashback' => [
                'safaricom' => (int) Setting::get('safaricom_cashback_pct', 80),
                'airtel' => (int) Setting::get('airtel_cashback_pct', 50),
            ],
            'bonga_rate' => (int) Setting::get('bonga_rate', 50),
            'min_payout_kes' => (int) Setting::get('min_payout_kes', 10),
            'bundles' => Bundle::where('active', true)
                ->orderBy('category')
                ->orderBy('sort_order')
                ->get(['id', 'category', 'label', 'price', 'validity_text']),
            'receiving_numbers' => ReceivingNumber::where('active', true)
                ->get(['network', 'msisdn']),
        ]);
    }
}
