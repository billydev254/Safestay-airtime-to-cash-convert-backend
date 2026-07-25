<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversion;
use App\Models\Setting;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Records an airtime-to-cash or Bonga-to-cash intent. No payment is
 * collected here — the user is sending us airtime/points, we pay cash out —
 * so this just logs intent and waits on the (still-manual, for now)
 * airtime-intake step before a payout is triggered.
 */
class ConversionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:airtime,bonga'],
            'network' => ['required_if:type,airtime', 'nullable', 'in:safaricom,airtel'],
            'sender_number' => ['required', 'string'],
            'mpesa_number' => ['required', 'string'],
            'amount_in' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $validated['sender_number'] = PhoneNumber::normalize($validated['sender_number']);
            $validated['mpesa_number'] = PhoneNumber::normalize($validated['mpesa_number']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $cashbackPct = $validated['type'] === 'airtime'
            ? (int) Setting::get("{$validated['network']}_cashback_pct", 0)
            : (int) Setting::get('bonga_rate', 0);

        $conversion = Conversion::create([
            ...$validated,
            'cashback_pct' => $cashbackPct,
            'amount_payout' => (int) round($validated['amount_in'] * $cashbackPct / 100),
            'status' => 'awaiting_intake',
        ]);

        return response()->json([
            'conversion_id' => $conversion->id,
            'amount_payout' => $conversion->amount_payout,
            'message' => 'Send your airtime/points now — cash is paid out once received.',
        ]);
    }
}
