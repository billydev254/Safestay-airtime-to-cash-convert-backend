<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\BundleOrder;
use App\Services\Daraja\DarajaManager;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class StkPushController extends Controller
{
    public function __construct(private readonly DarajaManager $daraja)
    {
    }

    /**
     * Initiates an STK push for a bundle purchase. AccountReference is the
     * recipient's number — the client's phone-based Bingwa automation reads
     * that off the resulting payment notification the same way it already
     * does for manually-entered Paybill payments.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bundle_id' => ['required', 'exists:bundles,id'],
            'recipient_number' => ['required', 'string'],
            'mpesa_number' => ['required', 'string'],
        ]);

        try {
            $recipientNumber = PhoneNumber::normalize($validated['recipient_number']);
            $mpesaNumber = PhoneNumber::normalize($validated['mpesa_number']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $bundle = Bundle::findOrFail($validated['bundle_id']);

        $order = BundleOrder::create([
            'bundle_id' => $bundle->id,
            'recipient_number' => $recipientNumber,
            'mpesa_number' => $mpesaNumber,
            'amount' => $bundle->price,
            'status' => 'pending_payment',
        ]);

        $result = $this->daraja->stkPush(
            phoneNumber: $mpesaNumber,
            amount: $bundle->price,
            accountReference: $recipientNumber,
            transactionDesc: "Bundle: {$bundle->label}",
            callbackUrl: route('webhooks.mpesa.stk-push.callback'),
        );

        $checkoutRequestId = $result['body']['CheckoutRequestID'] ?? null;

        if ($result['http_code'] !== 200 || ! $checkoutRequestId) {
            $order->update(['status' => 'failed']);

            return response()->json([
                'message' => $result['body']['errorMessage'] ?? 'Could not start payment. Please try again.',
            ], 422);
        }

        $order->update(['checkout_request_id' => $checkoutRequestId]);

        return response()->json([
            'order_id' => $order->id,
            'message' => 'Enter your M-Pesa PIN to complete the purchase.',
        ]);
    }

    public function callback(Request $request): JsonResponse
    {
        $stkCallback = $request->input('Body.stkCallback', []);
        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? null;

        $order = BundleOrder::where('checkout_request_id', $checkoutRequestId)->first();

        if ($order && $order->status === 'pending_payment') {
            if ((int) $resultCode === 0) {
                $items = collect($stkCallback['CallbackMetadata']['Item'] ?? [])->pluck('Value', 'Name');

                $order->update([
                    'status' => 'paid',
                    'mpesa_receipt' => $items->get('MpesaReceiptNumber'),
                ]);
            } else {
                $order->update(['status' => 'failed']);
            }
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
