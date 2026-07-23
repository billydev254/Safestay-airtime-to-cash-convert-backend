<?php

namespace App\Services\Payout;

use App\Models\Conversion;
use App\Services\Daraja\DarajaManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Triggers the B2C payout for a conversion once its airtime/Bonga intake is
 * confirmed. Only marks the conversion 'paying' and fires the request here —
 * final status ('paid'/'payout_failed') is set later by the B2C result
 * webhook, same acknowledge-first pattern the original PHP scaffolding used.
 */
class PayoutService
{
    public function __construct(private readonly DarajaManager $daraja)
    {
    }

    public function payout(Conversion $conversion): void
    {
        $originatorConversationId = (string) Str::uuid();

        $conversion->update([
            'status' => 'paying',
            'originator_conversation_id' => $originatorConversationId,
        ]);

        try {
            $result = $this->daraja->b2cPaymentRequest(
                originatorConversationId: $originatorConversationId,
                amount: $conversion->amount_payout,
                partyB: $conversion->mpesa_number,
                remarks: ucfirst($conversion->type).' to cash payout',
                queueTimeoutUrl: route('webhooks.mpesa.b2c.timeout'),
                resultUrl: route('webhooks.mpesa.b2c.result'),
                occasion: 'Safestay Deals',
            );
        } catch (Throwable $e) {
            // Network error, bad credentials, or anything else that never
            // even got a response from Daraja — no result callback will
            // ever arrive for this one, so mark it failed now rather than
            // leaving it stuck in "paying" forever.
            Log::error('b2c_payout_request_failed', ['conversion_id' => $conversion->id, 'message' => $e->getMessage()]);

            $conversion->update([
                'status' => 'payout_failed',
                'payout_result_desc' => 'Could not reach Daraja: '.$e->getMessage(),
            ]);

            return;
        }

        // Daraja rejected the request outright (bad credentials, validation
        // error) — no result callback will ever arrive for this one, so mark
        // it failed now instead of leaving it stuck in "paying" forever.
        if ($result['http_code'] !== 200 || (int) ($result['body']['ResponseCode'] ?? 1) !== 0) {
            $conversion->update([
                'status' => 'payout_failed',
                'payout_result_desc' => $result['body']['errorMessage'] ?? $result['body']['ResponseDescription'] ?? 'B2C request rejected',
            ]);
        }
    }
}
