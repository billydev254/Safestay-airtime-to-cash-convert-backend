<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\BundleOrder;
use App\Models\C2bPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * C2B Confirmation — called after the money has actually landed. Can't
 * reject here, only acknowledge.
 *
 * The old handler (c2b-confirmation/index.php) logged the payment and left
 * an explicit TODO: match it to a pending bundle order and mark it paid.
 * This fills that in. Delivery itself isn't triggered here — the client's
 * own phone automation handles that independently once the payment lands;
 * this is purely for our own records/admin visibility.
 */
class C2bConfirmationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $transactionId = $request->input('TransID');
        $amount = (int) $request->input('TransAmount');
        $billRefNumber = $request->input('BillRefNumber');

        try {
            C2bPayment::firstOrCreate(
                ['transaction_id' => $transactionId],
                [
                    'transaction_type' => $request->input('TransactionType'),
                    'trans_time' => $request->input('TransTime'),
                    'amount' => $amount,
                    'business_shortcode' => $request->input('BusinessShortCode'),
                    'bill_ref_number' => $billRefNumber,
                    'invoice_number' => $request->input('InvoiceNumber'),
                    'msisdn' => $request->input('MSISDN'),
                    'first_name' => $request->input('FirstName'),
                    'middle_name' => $request->input('MiddleName'),
                    'last_name' => $request->input('LastName'),
                    'raw_payload' => $request->getContent(),
                ]
            );

            BundleOrder::query()
                ->where('status', 'pending_payment')
                ->where('recipient_number', $billRefNumber)
                ->where('amount', $amount)
                ->first()
                ?->update([
                    'status' => 'paid',
                    'mpesa_receipt' => $transactionId,
                ]);
        } catch (Throwable $e) {
            Log::error('c2b_confirmation_error', ['message' => $e->getMessage()]);
        }

        // Daraja spec: C2B confirmation cannot reject, always acknowledge.
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }
}
