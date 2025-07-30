<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\PaymentStatusEnum;
use App\Exceptions\ForbiddenItemAccessException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\BrandGigPaymentResource;
use App\Http\Resources\Payment\BrandPaymentResource;
use App\Http\Resources\Payment\InfluencerPaymentResource;
use App\Models\EntityTrustapTransaction;
use App\Models\Gig;
use App\Services\v1\Payment\PaymentFailedException;
use App\Services\v1\Payment\TransactionFailedException;
use App\Services\v1\Payment\TrustapPaymentGateway;
use App\Services\v1\Payment\TrustAppException;
use App\Traits\PaginationTrait;
use Dom\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

// TODO: have to be logged in and have a data in UserTrustapMetadata
class TrustapController extends Controller
{
    use PaginationTrait;

    public $trustapPaymentGateway;

    public function __construct(TrustapPaymentGateway $trustapPaymentGateway)
    {
        $this->trustapPaymentGateway = $trustapPaymentGateway;
    }

    public function trustapCountryCodes(){
        $CCs = $this->trustapPaymentGateway->fetchSupportedCountryCodes();
        return $this->apiSuccess('supported country codes', $CCs);
    }

    public function createTransaction(Request $request, Gig $gig)
    {
        try {
            $validated = $request->validate([
                // 'buyer_id' => ['required', 'string'],
                // 'seller_id' => ['required', 'string'],
                // 'amount' => ['required', 'numeric'],
                // 'currency' => ['required', 'string'],
                'pricing_tier' => ['required', Rule::exists('pricing_tiers', 'id')],
                // 'role' => ['required', 'string', 'in:buyer,seller'],
                'description' => ['required', 'string', 'max:255'],
                // 'duration' => ['required','integer']
            ]);

            $redirectUrl = null;
            DB::transaction(function () use(&$redirectUrl, $validated, $gig){
                $redirectUrl = $this->trustapPaymentGateway->createTransaction($validated, $gig);
            });
            // Log::info($redirectUrl);
            // return redirect()->away($redirectUrl);
            return $this->apiSuccess('payment gateway url', ['trustap_url' => $redirectUrl]);
        } catch (TransactionFailedException $e) {
            return $this->apiError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error creating transaction: '.$e->getMessage());
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
            return $this->apiError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('An error occurred during payment callback: ' . $e->getMessage());
            return $this->apiError('An error occurred during payment processing.');
        }
    }

    public function sellerAcceptDeposit(Request $request, EntityTrustapTransaction $entityTrustapTransaction)
    {
        try {
            $response = $this->trustapPaymentGateway->sellerAcceptDeposit($entityTrustapTransaction);
            return $this->apiSuccess('Deposit accepted successfully.');
        } catch (PaymentFailedException $e) {
            return $this->apiError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('SellerAcceptDeposit: '.$e->getMessage());
            return $this->apiError('An error occurred while accepting the deposit: ');
        }
    }

    public function buyerConfirmsHandover(Request $request, EntityTrustapTransaction $entityTrustapTransaction)
    {
        try {
            $response = $this->trustapPaymentGateway->buyerConfirmsHandover($entityTrustapTransaction);
            return $this->apiSuccess('Handover confirmed successfully.');
        } catch (PaymentFailedException $e) {
            return $this->apiSuccess($e->getMessage());
        } catch (\Exception $e) {
            Log::error('BuyerConfirmsHandover: '.$e->getMessage());
            return $this->apiError('An error occurred while confirming the handover: ');
        }
    }

    public function buyerSubmitComplaint(Request $request, EntityTrustapTransaction $entityTrustapTransaction)
    {
        $validated = $request->validate([
            'complaint' => ['required', 'string', 'max:500'],
        ]);
        try {
            $response = $this->trustapPaymentGateway->buyerSubmitComplaint($entityTrustapTransaction, $validated['complaint']);
            return $this->apiSuccess('Complaint submitted successfully.');
        } catch (PaymentFailedException $e) {
            return $this->apiError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('Buyer Submit Complaint: '.$e->getMessage());

            return $this->apiError('An error occurred while submitting the complaint: '. $e->getMessage());
        }
    }

    public function sellerClaimsPayout(Request $request, EntityTrustapTransaction $entityTrustapTransaction)
    {
        try {
            $response = $this->trustapPaymentGateway->sellerClaimsPayout($entityTrustapTransaction);

            return $this->apiSuccess('Payout claimed successfully.');
        } catch (PaymentFailedException $e) {
            return $this->apiError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('SellerClaimsPayout: '.$e->getMessage());
            return $this->apiError('An error occurred while claiming the payout: ');
        }
    }
    
    public function fetchBrandTransaction(Request $request){
        $per_page = $request->query('per_page',10);
        $user_trustap_metadata = Auth::user()->userTrustapMetadata;
        $user_trustap_metadata = $user_trustap_metadata->guestUserTransactions()->where('status','!=',PaymentStatusEnum::TXN_INIT);
        $pagination = $user_trustap_metadata->with(['gig:id,user_id,title' => ['user'],'pricing:id,name,label'])
                        ->paginate($per_page);
        $transactions = $this->setupPagination($pagination, fn($items) => BrandPaymentResource::collection($items))->data;       
        return $this->apiSuccess('user(brand) transactions list', $transactions);
    }

    public function fetchInfluencerTransaction(Request $request){
        $per_page = $request->query('per_page',10);
        $sellerId = Auth::user()->userTrustapMetadata->trustapGuestUserId;
        $pagination = EntityTrustapTransaction::where('sellerId', $sellerId)
                        ->with(['gig','buyer:users.id,first_name,middle_name,last_name,nick_name,email','pricing:id,name,label'])
                        ->where('status', '!=', PaymentStatusEnum::TXN_INIT)
                        ->paginate($per_page);
        $transactions = $this->setupPagination($pagination, fn($items) => InfluencerPaymentResource::collection($items))->data;
        return $this->apiSuccess('user(influencer) transactions ', $transactions);
    }

    public function confirmDelivery(EntityTrustapTransaction $entityTrustapTransaction){
        $this->isOwner($entityTrustapTransaction);
        /* if (!empty($entityTrustapTransaction->complaintPeriodDeadline)) {
            return $this->apiError('item has already been delivered',409);
        } */
        if ($entityTrustapTransaction->status != PaymentStatusEnum::DEPOSIT_ACCEPTED->value) {
            return $this->apiError("could not change payment status(status can only be changed after deposit has been accepted)");
        }
        $entityTrustapTransaction->update([
            'status' => PaymentStatusEnum::DELIVERED->value,
            'delivered_at' => now(),
            'complaintPeriodDeadline' => now()->addDays(EntityTrustapTransaction::COMPLAIN_PERIOD_DEADLINE)
        ]);
        return $this->apiSuccess('item status changed to : delivered');
    }

    public function isOwner(EntityTrustapTransaction $entityTrustapTransaction){
        if ($entityTrustapTransaction->seller->isNot(Auth::user())) {
            throw new ForbiddenItemAccessException();
        }
    }
}