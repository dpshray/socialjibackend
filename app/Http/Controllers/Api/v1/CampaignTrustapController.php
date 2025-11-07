<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\PaymentStatusEnum;
use App\Exceptions\ForbiddenItemAccessException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\BrandPaymentResource;
use App\Http\Resources\Payment\Campaign\CampaignInfluencerPaymentCollection;
use App\Http\Resources\Payment\Campaign\CampaignPaymentCollection;
use App\Http\Resources\Payment\CampaignPaymentResource;
use App\Models\Bid;
use App\Models\Campaign;
use App\Models\CampaignEntityTrustapTransaction;
use App\Models\EntityTrustapTransaction;
use App\Services\v1\Payment\CampaignTrustapPaymentGateway;
use App\Services\v1\Payment\PaymentFailedException;
use App\Services\v1\Payment\TransactionFailedException;
use App\Services\v1\Payment\TrustapPaymentGateway;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CampaignTrustapController extends Controller
{

    use PaginationTrait;

    public $trustapPaymentGateway;

    public function __construct(CampaignTrustapPaymentGateway $trustapPaymentGateway)
    {
        $this->trustapPaymentGateway = $trustapPaymentGateway;
    }

    public function trustapCountryCodes()
    {
        $CCs = $this->trustapPaymentGateway->fetchSupportedCountryCodes();
        return $this->apiSuccess('supported country codes', $CCs);
    }

    public function createTransaction(Request $request, Bid $bid)
    {
        try {
            $validated = $request->validate([
                'description' => ['required', 'string', 'max:255'],
            ]);
            if (!$bid->is_selected) {
                return $this->apiError("Bid is not marked as 'selected' for campaign.",422);
            }
            /* $already_paid = CampaignEntityTrustapTransaction::where([
                ['bid_id', $bid->id],
                ['status',PaymentStatusEnum::AMOUNT_PAID->value]
            ])->exists();
            if ($already_paid) {
                return $this->apiError('this bid has already been processed.',422);
            } */
            $redirectUrl = null;
            DB::transaction(function () use (&$redirectUrl, $validated, $bid) {
                $redirectUrl = $this->trustapPaymentGateway->createTransaction($validated, $bid);
            });
            return $this->apiSuccess('payment gateway url', ['trustap_url' => $redirectUrl]);
        } catch (TransactionFailedException $e) {
            return $this->apiError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error creating transaction: ' . $e);
            return $this->apiError('An error occurred while creating the transaction.');
        }
    }

    public function paymentCallback(Request $request, $transaction_id)
    {
        try {
            $response = $request->merge(['tx_id' => $transaction_id])->all();
            $result = $this->trustapPaymentGateway->paymentSuccess($response);
            if (! $result) {
                return $this->apiError('Payment processing failed.');
            }
            return redirect(config('services.trustap.payment_success_redirection_url_to_site'));
        } catch (PaymentFailedException $e) {
            Log::info($e);
            return $this->apiError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('An error occurred during payment callback: ' . $e);
            return $this->apiError('An error occurred during payment processing.');
        }
    }

    public function bidderAcceptDeposit(Request $request, CampaignEntityTrustapTransaction $campaignEntityTrustapTransaction)
    {
        try {
            $response = $this->trustapPaymentGateway->bidderAcceptDeposit($campaignEntityTrustapTransaction);
            return $this->apiSuccess('Deposit accepted successfully.');
        } catch (PaymentFailedException $e) {
            return $this->apiError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('SellerAcceptDeposit: ' . $e->getMessage());
            return $this->apiError('An error occurred while accepting the deposit: ');
        }
    }

    public function confirmDelivery(CampaignEntityTrustapTransaction $campaignEntityTrustapTransaction)
    {
        // $this->isOwner($campaignEntityTrustapTransaction);
        if (Auth::id() != $campaignEntityTrustapTransaction->bid->bidder_id) {
            throw new ForbiddenItemAccessException();
        }
        /* if (!empty($campaignEntityTrustapTransaction->complaintPeriodDeadline)) {
            return $this->apiError('item has already been delivered',409);
        } */
        if ($campaignEntityTrustapTransaction->status != PaymentStatusEnum::DEPOSIT_ACCEPTED->value) {
            return $this->apiError("could not change payment status(status can only be changed after deposit has been accepted)");
        }
        $campaignEntityTrustapTransaction->update([
            'status' => PaymentStatusEnum::DELIVERED->value,
            'delivered_at' => now(),
            'complaintPeriodDeadline' => now()->addDays(EntityTrustapTransaction::COMPLAIN_PERIOD_DEADLINE)
        ]);
        return $this->apiSuccess('item status changed to : delivered');
    }

    public function buyerConfirmsHandover(Request $request, CampaignEntityTrustapTransaction $campaignEntityTrustapTransaction)
    {
        try {
            $response = $this->trustapPaymentGateway->buyerConfirmsHandover($campaignEntityTrustapTransaction);
            return $this->apiSuccess('Handover confirmed successfully.');
        } catch (PaymentFailedException $e) {
            return $this->apiSuccess($e->getMessage());
        } catch (\Exception $e) {
            Log::error('BuyerConfirmsHandover: ' . $e->getMessage());
            return $this->apiError('An error occurred while confirming the handover: ');
        }
    }

    public function buyerSubmitComplaint(Request $request, CampaignEntityTrustapTransaction $campaignEntityTrustapTransaction)
    {
        $validated = $request->validate([
            'complaint' => ['required', 'string', 'max:500'],
        ]);
        try {
            $response = $this->trustapPaymentGateway->buyerSubmitComplaint($campaignEntityTrustapTransaction, $validated['complaint']);
            return $this->apiSuccess('Complaint submitted successfully.');
        } catch (PaymentFailedException $e) {
            return $this->apiError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('Buyer Submit Complaint: ' . $e->getMessage());

            return $this->apiError('An error occurred while submitting the complaint: ' . $e->getMessage());
        }
    }

    public function bidderClaimsPayout(Request $request, CampaignEntityTrustapTransaction $campaignEntityTrustapTransaction)
    {
        try {
            $response = $this->trustapPaymentGateway->bidderClaimsPayout($campaignEntityTrustapTransaction);
            return $this->apiSuccess('Payout claimed successfully.');
        } catch (PaymentFailedException $e) {
            return $this->apiError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('SellerClaimsPayout: ' . $e->getMessage());
            return $this->apiError('An error occurred while claiming the payout: ');
        }
    }

    public function isOwner(CampaignEntityTrustapTransaction $campaignEntityTrustapTransaction)
    {
        if ($campaignEntityTrustapTransaction->seller->isNot(Auth::user())) {
            throw new ForbiddenItemAccessException();
        }
    }

    function getAssignedBidderList(Request $request) { #BRAND
        $per_page = $request->query('per_page');
                $pagination = Bid::with(['trustapTransaction' => fn($qry) => $qry->where('status','<>', PaymentStatusEnum::TXN_INIT->value),'bidder','campaign'])
            ->where('is_selected',true)
            ->whereRelation('campaign','brand_id',Auth::id())
            ->orderBy('id','DESC')
            ->paginate($per_page);
        // $pagination = CampaignEntityTrustapTransaction::with(['bid.bidder', 'bid.campaign'])
        //     ->whereRelation('campaign','brand_id',Auth::id())
        //     ->where('status', '<>',PaymentStatusEnum::TXN_INIT->value)
        //     ->paginate($per_page);
        $campaign = $this->setupPagination($pagination, CampaignPaymentCollection::class)->data;
        return $this->apiSuccess('List of all influencer bids', $campaign);
    }

    function fetchBidStatus(Request $request) {
        $per_page = $request->query('per_page');
        $pagination = CampaignEntityTrustapTransaction::with(['bid.campaign.brand'])
            ->whereRelation('bid', 'bidder_id', Auth::id())
            ->where('status', '<>', PaymentStatusEnum::TXN_INIT->value)
            ->paginate($per_page);
        $bids = $this->setupPagination($pagination, CampaignInfluencerPaymentCollection::class)->data;
        return $this->apiSuccess('List of all influencer bids', $bids);
    }

    function fetchBrandTransactionNoTrustapUser(Request $request) {
        $per_page = $request->query('per_page');
        $pagination = Bid::where('bidder_id',Auth::id())->paginate($per_page);
        $bids = $this->setupPagination($pagination, CampaignInfluencerPaymentCollection::class)->data;
        return $this->apiSuccess('List of all influencer bids(no trustap user)', $bids);
    }
}
