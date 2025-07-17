<?php

namespace App\Services\v1\Payment;

use App\Constants\Constants;
use App\Models\EntityTrustapTransaction;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrustapPaymentGateway
{
    private string $buyerId;

    private string $sellerId;

    private string $transactionId;

    public function __construct() {}

    public function getTrustapFee(int $price, string $currency)
    {
        $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->get(config('services.trustap.url').'/p2p/charge', [
                'price' => $price,
                'currency' => $currency,
            ]);
        if ($response->failed() && $response->status() == 400) {
            $error_to_object = json_decode($response->body());
            throw new TrustAppException($error_to_object->error);
        }
        return $response->json();
    }

    public function createTransaction(array $data, $gig)
    {
        if (auth()->user()->isInfluencer()) {
            throw new TransactionFailedException('Influencer cannot create transactions.');
        }

        $buyerId = auth()->user()->userTrustapMetadata->trustap_user_id;
        $sellerId = $gig->user?->userTrustapMetadata?->trustapGuestUserId;
        
        if (! $buyerId || ! $sellerId) {
            throw new Exception('Buyer or Seller Trustap user not found.');
        }

        $gigPricing = $gig->gig_pricing->where('id', $data['pricing_tier'])->first();
        $currency_code = strtolower($gigPricing->pivot->currency->code);
        // dd($currency_code);
        $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Trustap-User' => $sellerId,
            ])
            ->post(config('services.trustap.url').'/p2p/me/transactions/create_with_guest_user', [
                'seller_id' => $sellerId,
                'buyer_id' => $buyerId,
                'creator_role' => $data['role'],
                'currency' => $currency_code,
                'description' => $data['description'],
                'deposit_price' => (int) $gigPricing->pivot->price,
                'deposit_charge' => $this->getTrustapFee($gigPricing->pivot->price, $currency_code)['charge'],
                'charge_calculator_version' => 3,
                'skip_remainder' => true,
                'duration' => $data['duration']
            ]);

        $response = $response->json();
        if (isset($response['error'])) {
            logError(__METHOD__, func_get_args(), $response, 'Failed to create transaction.');
            throw new Exception('Failed to create transaction: '.$response['error']);
        }
        Log::debug('createTransaction : ',$response);
        
        $transaction = EntityTrustapTransaction::create([
            'gig_id' => $gig->id,
            'gig_pricing_id' => $gigPricing->id,
            'gig_title' => $gig->title,
            'transactionId' => $response['id'],
            'transactionType' => 'f2f', // or set as needed
            'sellerId' => $response['seller_id'],
            'buyerId' => $response['buyer_id'],
            'status' => $response['status'],
            'price' => (int) $response['deposit_pricing']['price'],
            'charge' => (int) $response['deposit_pricing']['charge'],
            'chargeSeller' => (int) $response['deposit_pricing']['charge_seller'],
            'currency' => $response['currency'],
            'description' => $response['description'],
        ]);

        return config('services.trustap.payment_action')."/f2f/transactions/$transaction->transactionId/pay_deposit?redirect_uri=".config('services.trustap.payment_callback_uri');
    }

    public function paymentSuccess(array $data)
    {
        // Step 1: Get the transaction ID using the join
        $transaction_joined_query = EntityTrustapTransaction::select('entity_trustap_transactions.id as et_transaction_id', 'user_id')
                            ->join(
                                'user_trustap_metadata',
                                'user_trustap_metadata.trustapGuestUserId',
                                '=',
                                'entity_trustap_transactions.buyerId'
                            )
                            ->where('transactionId', $data['tx_id'])
                            ->firstOrFail();
            // ->value('entity_trustap_transactions.id'); // Only get the ID
            // dd($transaction);
        // Step 2: Load the Eloquent model
        // Step 3: Use the Eloquent model as needed
        $data['user_id'] = $transaction_joined_query->user_id;
        
        if ($data['trustap_status'] !== 'ok') {
            logError(__METHOD__, func_get_args(), $data, 'Payment Failed.');
            throw new PaymentFailedException('Payment failed. Please try again.');
        }
        
        logInfo(__METHOD__, func_get_args(), $data, 'Payment Success.');
        return EntityTrustapTransaction::findOrFail($transaction_joined_query->et_transaction_id)
                ->update([
                    'status' => $data['code'],
                ]);
    }

    // public function transferFundsByCards()
    // {
    //     $transactionId = '26383';
    //     // $response = Http::get(config('services.trustap.payment_action') . "/f2f/transactions/$transactionId/pay_deposit", [
    //     //     'redirect_uri' => config('services.trustap.payment_callback_uri'),
    //     // ]);

    //     return redirect()->away(config('services.trustap.payment_action') . "/f2f/transactions/$transactionId/pay_deposit?redirect_uri=" . config('services.trustap.payment_callback_uri'));
    //     return Http::get("https://actions.stage.trustap.com/f2f/transactions/25973/pay_deposit?redirect_uri=http://localhost:8000/trustap/payment/callback");
    //     dd($response);
    //     $data = $response->json();
    //     dd($data);
    // }

    // public function transferFundsByBank()
    // {
    //     $transactionId = 26381;;

    //     $setDepositPaymentMethod = Http::withBasicAuth(config('services.trustap.api_key'), '')
    //         ->withHeaders([
    //             'Content-Type' => 'application/json',
    //         ])
    //         ->post("https://dev.stage.trustap.com/api/v1/p2p/transactions/$transactionId/set_deposit_payment_method", [
    //             'currency' => 'gbp',
    //             'deposit_charge' => 110,
    //             'deposit_charge_seller' => 0,
    //             'deposit_price' => 1000,
    //             'payment_method' => 'bank_transfer',
    //         ]);

    //     logInfo(__METHOD__, func_get_args(), $setDepositPaymentMethod->json(), 'Set Deposit Payment');

    //     $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
    //         ->get("https://dev.stage.trustap.com/api/v1/p2p/transactions/$transactionId/bank_transfer_details");

    //     $data = $response->json();
    //     dd($data);
    // }

    public function sellerAcceptDeposit($entityTrustapTransaction)
    {
        if (auth()->user()->userTrustapMetadata->trustapGuestUserId != $entityTrustapTransaction->sellerId) {
            throw new PaymentFailedException('You are not authorized to accept this deposit.');
        }

        $sellerId = auth()->user()->userTrustapMetadata->trustapGuestUserId;

        $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Trustap-User' => $sellerId,
            ])
            ->post(config('services.trustap.url')."/p2p/transactions/$entityTrustapTransaction->transactionId/accept_deposit_with_guest_seller");

        $response = $response->json();

        if (isset($response['error'])) {
            logError(__METHOD__, func_get_args(), $response, $response['error']);
            throw new PaymentFailedException('Failed to accept deposit: ');
        }
        Log::debug('sellerAcceptDeposit : ', $response);

        logInfo(__METHOD__, func_get_args(), $response, 'Seller Accept Deposit Successfully.');

        return $entityTrustapTransaction->update([
            'status' => $response['status'],
        ]);

    }

    public function buyerConfirmsHandover(EntityTrustapTransaction $entityTrustapTransaction)
    {
        if (auth()->user()->userTrustapMetadata->trustapGuestUserId != $entityTrustapTransaction->buyerId) {
            throw new PaymentFailedException('You are not authorized to confirm the handover.');
        }

        $buyerId = auth()->user()->userTrustapMetadata->trustap_user_id;
        $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Trustap-User' => $buyerId,
            ])
            ->post(config('services.trustap.url')."/p2p/transactions/$entityTrustapTransaction->transactionId/confirm_handover_with_guest_user");

        $response = $response->json();
            
        if (isset($response['error'])) {
            logError(__METHOD__, func_get_args(), $response, $response['error']);
            throw new PaymentFailedException('Failed to confirm handover.');
        }
        Log::debug('buyerConfirmsHandover : ', $response);
        logInfo(__METHOD__, func_get_args(), $response, 'Buyer Confirms Handover Successfully.');

        return $entityTrustapTransaction->update([
            'status' => $response['status'],
        ]);

    }

    public function buyerSubmitComplaint($entityTrustapTransaction, $complaint)
    {
        if (auth()->user()->userTrustapMetadata->trustapGuestUserId != $entityTrustapTransaction->buyerId) {
            throw new PaymentFailedException('You are not authorized to submit complaint on this transaction.');
        }

        $buyerId = auth()->user()->userTrustapMetadata->trustapGuestUserId;

        $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Trustap-User' => $buyerId,
            ])
            ->post(config('services.trustap.url')."/p2p/transactions/{$entityTrustapTransaction->transactionId}/complain_with_guest_buyer", [
                'description' => $complaint,
            ]);

        $data = $response->json();

        if (isset($data['error'])) {
            dd($data);
            logError(__METHOD__, func_get_args(), $data, 'Failed to submit complaint.');
            throw new PaymentFailedException('Failed to submit complaint.');
        }

        logInfo(__METHOD__, func_get_args(), $data, 'Complaint submitted successfully');

        return $entityTrustapTransaction->update([
            'status' => $data['status'] ?? $entityTrustapTransaction->status,
        ]);
    }

    public function sellerClaimsPayout($entityTrustapTransaction)
    {
        if (auth()->user()->userTrustapMetadata->trustapGuestUserId != $entityTrustapTransaction->sellerId) {
            throw new PaymentFailedException('You are not authorized to claim this transaction.');
        }

        $sellerId = auth()->user()->userTrustapMetadata->trustapFullUserId;
        $trustapUserType = auth()->user()->userTrustapMetadata->trustap_user_type;

        if ($trustapUserType !== Constants::TRUSTAP_FULL_USER) {
            throw new PaymentFailedException('Only full Trustap users can claim payouts.');
        }

        $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Trustap-User' => $sellerId,
            ])
            ->post(config('services.trustap.url')."/p2p/transactions/$entityTrustapTransaction->transactionId/claim_for_seller");

        $data = $response->json();

        if (isset($data['error'])) {
            logError(__METHOD__, func_get_args(), $data, 'Failed to claim payout.');
            throw new PaymentFailedException('Failed to claim payout.');
        }

        logInfo(__METHOD__, func_get_args(), $data, 'Seller claims payout.');

        return $entityTrustapTransaction->update([
            'status' => $data['status'],
        ]);
    }
}
