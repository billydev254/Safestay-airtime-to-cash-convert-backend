<?php

namespace App\Services\Daraja;

/**
 * Wires up the two shortcodes the client uses:
 * - Till (Buy Goods): bundle purchases — STK push + C2B collection
 * - Paybill: airtime-to-cash / Bonga payouts — B2C requires an org/paybill
 *   shortcode, Till numbers can't do B2C. See Phase 1 plan for why these
 *   are kept separate rather than sharing one shortcode/client.
 */
class DarajaManager
{
    public function till(): DarajaClient
    {
        return new DarajaClient(
            config('daraja.till.consumer_key'),
            config('daraja.till.consumer_secret'),
            config('daraja.base_url'),
        );
    }

    public function paybill(): DarajaClient
    {
        return new DarajaClient(
            config('daraja.paybill.consumer_key'),
            config('daraja.paybill.consumer_secret'),
            config('daraja.base_url'),
        );
    }

    /**
     * STK push for a bundle purchase, against the till. AccountReference is
     * set to the recipient's number — the same trick the old site used via
     * manual Paybill entry, now done programmatically.
     */
    public function stkPush(string $phoneNumber, int $amount, string $accountReference, string $transactionDesc, string $callbackUrl): array
    {
        $shortcode = config('daraja.till.shortcode');
        $storeNumber = config('daraja.till.store_number');
        $passkey = config('daraja.till.passkey');
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($shortcode.$passkey.$timestamp);

        return $this->till()->post('/mpesa/stkpush/v1/processrequest', [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerBuyGoodsOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => $storeNumber,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc,
        ]);
    }

    /**
     * B2C payout for an airtime-to-cash/Bonga conversion, against the paybill.
     */
    public function b2cPaymentRequest(string $originatorConversationId, int $amount, string $partyB, string $remarks, string $queueTimeoutUrl, string $resultUrl, string $occasion = ''): array
    {
        return $this->paybill()->post('/mpesa/b2c/v3/paymentrequest', [
            'OriginatorConversationID' => $originatorConversationId,
            'InitiatorName' => config('daraja.paybill.initiator_name'),
            'SecurityCredential' => config('daraja.paybill.security_credential'),
            'CommandID' => 'BusinessPayment',
            'Amount' => $amount,
            'PartyA' => config('daraja.paybill.shortcode'),
            'PartyB' => $partyB,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $queueTimeoutUrl,
            'ResultURL' => $resultUrl,
            'Occassion' => $occasion,
        ]);
    }

    /**
     * Register C2B validation/confirmation URLs against the till.
     */
    public function registerC2bUrl(string $confirmationUrl, string $validationUrl): array
    {
        return $this->till()->post('/mpesa/c2b/v2/registerurl', [
            'ShortCode' => config('daraja.till.shortcode'),
            'ResponseType' => 'Completed',
            'ConfirmationURL' => $confirmationUrl,
            'ValidationURL' => $validationUrl,
        ]);
    }
}
