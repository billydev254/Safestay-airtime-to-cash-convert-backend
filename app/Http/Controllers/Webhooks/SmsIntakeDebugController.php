<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Temporary: logs whatever the SMS-forwarding app actually sends so we can
 * see its real payload shape before building the real parser + auto-payout
 * logic. Delete once SmsIntakeController replaces it.
 */
class SmsIntakeDebugController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Log::info('sms_intake_debug_payload', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw' => $request->getContent(),
        ]);

        return response()->json(['status' => 'logged']);
    }
}
