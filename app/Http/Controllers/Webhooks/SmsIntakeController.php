<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\AirtimeIntake\SmsIntakeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives forwarded SMS from the SMS-forwarding app on the receiving line.
 * Guarded by a shared secret since a successful call here ends in a real
 * M-Pesa payout — anyone reaching this without the secret only ever
 * produces a rejected match, never a payout.
 */
class SmsIntakeController extends Controller
{
    public function __invoke(Request $request, SmsIntakeService $service): JsonResponse
    {
        if (! hash_equals((string) config('services.sms_intake.secret'), (string) $request->header('X-Sms-Intake-Secret'))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $service->handle(
            smsText: (string) $request->input('key'),
            fromSender: (string) $request->input('from', ''),
        );

        return response()->json(['status' => 'ok']);
    }
}
