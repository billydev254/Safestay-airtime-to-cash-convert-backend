<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\B2cResult;
use App\Models\Conversion;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * B2C Result + Queue Timeout — consolidates what used to be three
 * near-identical files (index.php, indextimeout.php, b2c-result-index.php)
 * with inconsistent log paths into one handler, bound to two named routes.
 */
class B2cCallbackController extends Controller
{
    public function result(Request $request): JsonResponse
    {
        return $this->handle($request);
    }

    public function timeout(Request $request): JsonResponse
    {
        return $this->handle($request);
    }

    private function handle(Request $request): JsonResponse
    {
        $result = $request->input('Result', []);

        $originatorConversationId = $result['OriginatorConversationID'] ?? null;
        $conversationId = $result['ConversationID'] ?? null;
        $transactionId = $result['TransactionID'] ?? null;
        $resultCode = $result['ResultCode'] ?? null;
        $resultDesc = $result['ResultDesc'] ?? null;

        $params = collect($result['ResultParameters']['ResultParameter'] ?? [])
            ->pluck('Value', 'Key');

        try {
            $created = B2cResult::firstOrCreate(
                ['originator_conversation_id' => $originatorConversationId],
                [
                    'conversation_id' => $conversationId,
                    'transaction_id' => $transactionId,
                    'result_code' => $resultCode,
                    'result_desc' => $resultDesc,
                    'amount' => $params->get('TransactionAmount'),
                    'receipt' => $params->get('TransactionReceipt'),
                    'receiver_name' => $params->get('ReceiverPartyPublicName'),
                    'utility_balance' => $params->get('B2CUtilityAccountAvailableFunds'),
                    'completed_at' => $params->get('TransactionCompletedDateTime'),
                    'raw_payload' => $request->getContent(),
                ]
            );

            if ($created->wasRecentlyCreated) {
                Conversion::query()
                    ->where('originator_conversation_id', $originatorConversationId)
                    ->where('status', 'paying')
                    ->update([
                        'status' => (int) $resultCode === 0 ? 'paid' : 'payout_failed',
                        'mpesa_receipt' => $params->get('TransactionReceipt'),
                        'payout_result_desc' => $resultDesc,
                    ]);

                if ($params->get('B2CUtilityAccountAvailableFunds')) {
                    Setting::set('utility_balance', $params->get('B2CUtilityAccountAvailableFunds'));
                }
            }
        } catch (Throwable $e) {
            Log::error('b2c_callback_error', ['message' => $e->getMessage()]);
        }

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }
}
