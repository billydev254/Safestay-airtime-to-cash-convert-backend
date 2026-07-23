<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\BundleOrder;
use App\Models\C2bValidationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * C2B Validation — Safaricom calls this the moment a customer initiates
 * payment, before the money moves. We can accept or reject.
 *
 * The old stub (c2b-validation/index.php) accepted everything unconditionally.
 * This replaces that with a real rule: the BillRefNumber (the recipient's
 * number, entered as the M-Pesa account reference) plus TransAmount must
 * match a bundle_order we're actually expecting payment for.
 */
class C2bValidationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $transactionId = $request->input('TransID');
        $msisdn = $request->input('MSISDN');
        $amount = (int) $request->input('TransAmount');
        $billRefNumber = $request->input('BillRefNumber');

        $matches = BundleOrder::query()
            ->where('status', 'pending_payment')
            ->where('recipient_number', $billRefNumber)
            ->where('amount', $amount)
            ->exists();

        $decision = $matches ? 'accepted' : 'rejected';

        C2bValidationLog::create([
            'transaction_id' => $transactionId,
            'msisdn' => $msisdn,
            'amount' => $amount,
            'decision' => $decision,
            'raw_payload' => $request->getContent(),
        ]);

        if ($decision === 'rejected') {
            return response()->json([
                'ResultCode' => 'C2B00016',
                'ResultDesc' => 'Rejected',
            ]);
        }

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }
}
