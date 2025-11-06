<?php

namespace App\Services\v1\Payment;

use App\Constants\Constants;
use App\Enums\PaymentStatusEnum;
use App\Exceptions\ForbiddenItemAccessException;
use App\Models\Bid;
use App\Models\CampaignEntityTrustapTransaction;
use App\Models\EntityTrustapTransaction;
use App\Models\Gig;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\UnauthorizedException;

class CampaignTrustapPaymentGateway
{
    private string $buyerId;

    private string $sellerId;

    private string $transactionId;

    public function __construct() {}

    public function fetchSupportedCountryCodes(){
        try {
            // throw new \Exception('A TEST EXCEPTION');
            return Cache::remember('payment_country_codes', 86400, function () {
                $api_country_code = Http::withBasicAuth(config('services.trustap.api_key'), '')
                    ->get(config('services.trustap.url').'client/supported_registration_countries')
                    ->json();
                $countries = [
                    'at' => 'Austria',
                    'au' => 'Australia',
                    'be' => 'Belgium',
                    'bg' => 'Bulgaria',
                    'ca' => 'Canada',
                    'ch' => 'Switzerland',
                    'cy' => 'Cyprus',
                    'cz' => 'Czech Republic',
                    'de' => 'Germany',
                    'dk' => 'Denmark',
                    'ee' => 'Estonia',
                    'es' => 'Spain',
                    'fi' => 'Finland',
                    'fr' => 'France',
                    'gb' => 'United Kingdom',
                    'gr' => 'Greece',
                    'hr' => 'Croatia',
                    'hu' => 'Hungary',
                    'ie' => 'Ireland',
                    'it' => 'Italy',
                    'lt' => 'Lithuania',
                    'lu' => 'Luxembourg',
                    'lv' => 'Latvia',
                    'mt' => 'Malta',
                    'nl' => 'Netherlands',
                    'no' => 'Norway',
                    'pl' => 'Poland',
                    'pt' => 'Portugal',
                    'ro' => 'Romania',
                    'se' => 'Sweden',
                    'si' => 'Slovenia',
                    'sk' => 'Slovakia',
                    'us' => 'United States',
                ];
                return array_intersect(array_flip($countries), $api_country_code);
            });
        } catch (\Exception $e) {
            logError(__METHOD__, func_get_args(), $e->getMessage(), 'Error while fetching country codes.');
            throw new TrustAppException("Error while fetching country codes.");    
        }
    }

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

    public function createTransaction(array $data, Bid $bid)
    {
        if (Auth::user()->isInfluencer()) {
            throw new TransactionFailedException('Influencer cannot create transactions.');
        }
        $buyerId = $bid->bidder->userTrustapMetadata->trustap_user_id;#guestUserId
        $sellerId = $bid->campaign->brand->userTrustapMetadata->trustapGuestUserId;
        if (! $buyerId || ! $sellerId) {
            throw new Exception('Buyer or Seller Trustap user not found.');
        }
        $bid_campaign = $bid->campaign;
        if ($bid_campaign->brand_id != Auth::id()) {
            throw new UnauthorizedException("Campaign belongs to another user.");
        }
        $price = $bid->bid;
        $currency_code = strtolower($bid_campaign->currency->code);
        $item_amount = (int) $price;
        $amount_in_cent = $item_amount * 100;
        // dd($currency_code);
        $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Trustap-User' => $buyerId,
            ])
            ->post(config('services.trustap.url').'/p2p/me/transactions/create_with_guest_user', [
                'seller_id' => $sellerId,
                'buyer_id' => $buyerId,
                // 'creator_role' => $data['role'],
                'creator_role' => 'seller',
                'currency' => $currency_code,
                'description' => $data['description'],
                'deposit_price' => $amount_in_cent,
                'deposit_charge' => $this->getTrustapFee($amount_in_cent, $currency_code)['charge'],
                'charge_calculator_version' => 3,
                'skip_remainder' => true
            ]);

        $response = $response->json();
        if (isset($response['error'])) {
            logError(__METHOD__, func_get_args(), $response, 'Failed to create transaction.');
            throw new Exception('Failed to create transaction: '.$response['error']);
        }
        // Log::debug('createTransaction : ',$response);
        $transaction = CampaignEntityTrustapTransaction::create([
            'campaign_id' => $bid_campaign->id,
            'bidder_id' => $bid->bidder_id,
            'bid_id' => $bid->id,
            // 'gig_pricing_id' => $gigPricing->pivot->id,
            'campaign_title' => $bid_campaign->title,
            'transactionId' => $response['id'],
            'transactionType' => 'f2f', // or set as needed
            'sellerId' => $response['seller_id'],
            'buyerId' => $response['buyer_id'],
            // 'status' => $response['status'],
            'status' => PaymentStatusEnum::TXN_INIT->value,
            'price' => (int) $response['deposit_pricing']['price'] / 100,# converting back form cent
            'charge' => (int) $response['deposit_pricing']['charge'] / 100,
            'chargeSeller' => (int) $response['deposit_pricing']['charge_seller'],
            'currency' => $response['currency'],
            'description' => $response['description'],
        ]);
        return config('services.campaign_trustap.payment_action')."/f2f/transactions/$transaction->transactionId/pay_deposit?redirect_uri=". config('services.campaign_trustap.payment_callback_uri').'/'. $response['id'];
    }

    public function paymentSuccess(array $data)
    {
        $transaction = CampaignEntityTrustapTransaction::where('transactionId', $data['tx_id'])->firstOrFail();
        $data['user_id'] = $transaction->buyer->id; 
        if ($data['trustap_status'] !== 'ok') {
            logError(__METHOD__, func_get_args(), $data, 'Payment Failed.');
            throw new PaymentFailedException('Payment failed. Please try again.');
        }
        logInfo(__METHOD__, func_get_args(), $data, 'Payment Success.');
        if ($transaction->status == PaymentStatusEnum::AMOUNT_PAID->value) {
            throw new PaymentFailedException('Item has already been paid.');
        }

        return $transaction->update([
            'status' => PaymentStatusEnum::AMOUNT_PAID->value,
        ]);
    }

    public function bidderAcceptDeposit(CampaignEntityTrustapTransaction $entityTrustapTransaction)
    {
        $user_trustap_meta_data = Auth::user()->userTrustapMetadata;
        $bidder_id = $user_trustap_meta_data->trustapGuestUserId; 
        if ($bidder_id != $entityTrustapTransaction->buyerId) {
            throw new PaymentFailedException('You are not authorized to accept this deposit.');
        }elseif ($entityTrustapTransaction->status == PaymentStatusEnum::DEPOSIT_ACCEPTED->value) {
            throw new PaymentFailedException('deposited has already been accepted.');
        }elseif ($entityTrustapTransaction->status != PaymentStatusEnum::AMOUNT_PAID->value) {
            throw new \Exception("could not change payment status(status can only be changed after amount has been paid)");
        }
        // dd($bidder_id);
        $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Trustap-User' => '1-29aa848b-7a0b-4095-9973-13041dfee2d7',
            ])
            ->post(config('services.trustap.url')."/p2p/transactions/$entityTrustapTransaction->transactionId/accept_deposit_with_guest_seller");

        $response = $response->json();

        if (isset($response['error'])) {
            logError(__METHOD__, func_get_args(), $response, $response['error']);
            throw new PaymentFailedException('Failed to accept deposit: ');
        }
        // Log::debug('sellerAcceptDeposit : ', $response);

        logInfo(__METHOD__, func_get_args(), $response, 'Seller Accept Deposit Successfully.');

        return $entityTrustapTransaction->update([
            'status' => PaymentStatusEnum::DEPOSIT_ACCEPTED->value,
        ]);

    }

    public function buyerConfirmsHandover(EntityTrustapTransaction $entityTrustapTransaction)
    {
        if (Auth::user()->userTrustapMetadata->trustapGuestUserId != $entityTrustapTransaction->buyerId) {
            throw new PaymentFailedException('You are not authorized to confirm the handover.');
        }elseif ($entityTrustapTransaction->status == PaymentStatusEnum::HANDOVERED->value) {
            throw new PaymentFailedException('item is already handovered.');
        } elseif ($entityTrustapTransaction->status != PaymentStatusEnum::DELIVERED->value) {
            throw new \Exception("could not change payment status(status can only be changed after amount has been delivered)");
        }

        $buyerId = Auth::user()->userTrustapMetadata->trustap_user_id;
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
        // Log::debug('buyerConfirmsHandover : ', $response);
        logInfo(__METHOD__, func_get_args(), $response, 'Buyer Confirms Handover Successfully.');

        return $entityTrustapTransaction->update([
            'status' => PaymentStatusEnum::HANDOVERED->value,
        ]);

    }

    public function buyerSubmitComplaint(EntityTrustapTransaction $entityTrustapTransaction, $complaint)
    {
        if (Auth::user()->userTrustapMetadata->trustapGuestUserId != $entityTrustapTransaction->buyerId) {
            throw new PaymentFailedException('You are not authorized to submit complaint on this transaction.');
        }
        elseif ($entityTrustapTransaction->status == PaymentStatusEnum::COMPLAINED->value) {
            throw new \Exception("item has already been complained");
        }
        elseif ($entityTrustapTransaction->status != PaymentStatusEnum::HANDOVERED->value) {
            throw new \Exception("complain can only be possible after item has been delivered");
        }
        elseif ($entityTrustapTransaction->delivered_at->lt(now())) {
            $hour_to_wait = EntityTrustapTransaction::COMPLAINT_PERIOD_DAYS_AFTER_DELIVERY * 24;
            throw new \Exception('please wait for '. $hour_to_wait.' hour after delivery time to make a complain for this item');
        }
        elseif ($entityTrustapTransaction->delivered_at->gte(now())) {
            throw new PaymentFailedException('Complaint period has already been expired.');            
        }

        $buyerId = Auth::user()->userTrustapMetadata->trustapGuestUserId;

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
            logError(__METHOD__, func_get_args(), $data, 'Failed to submit complaint.');
            throw new PaymentFailedException($data['error']);
        }

        logInfo(__METHOD__, func_get_args(), $data, 'Complaint submitted successfully');

        return $entityTrustapTransaction->update([
            'status' => PaymentStatusEnum::COMPLAINED->value ?? $entityTrustapTransaction->status,
        ]);
    }

    public function bidderClaimsPayout(CampaignEntityTrustapTransaction $entityTrustapTransaction)
    {
        if (Auth::user()->userTrustapMetadata->trustapGuestUserId != $entityTrustapTransaction->buyerId) {
            throw new PaymentFailedException('You are not authorized to claim this transaction.');
        }elseif ($entityTrustapTransaction->status == PaymentStatusEnum::AMOUNT_CLAIMED->value) {
            throw new \Exception('amount has already been claimed');
        }

        $bidderId = Auth::user()->userTrustapMetadata->trustapFullUserId;
        $trustapUserType = Auth::user()->userTrustapMetadata->trustap_user_type;

        if ($trustapUserType !== Constants::TRUSTAP_FULL_USER) {
            throw new PaymentFailedException('Only full Trustap users can claim payouts.');
        }

        $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Trustap-User' => $bidderId,
            ])
            ->post(config('services.trustap.url')."/p2p/transactions/$entityTrustapTransaction->transactionId/claim_for_seller");

        $data = $response->json();

        if (isset($data['error'])) {
            logError(__METHOD__, func_get_args(), $data, 'Failed to claim payout.');
            throw new PaymentFailedException('Failed to claim payout.');
        }

        logInfo(__METHOD__, func_get_args(), $data, 'Seller claims payout.');

        return $entityTrustapTransaction->update([
            'status' => PaymentStatusEnum::AMOUNT_CLAIMED->value,
        ]);
    }
}
