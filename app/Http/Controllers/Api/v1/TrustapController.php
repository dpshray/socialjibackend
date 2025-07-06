<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\EntityTrustapTransaction;
use App\Models\Gig;
use App\Services\v1\Payment\PaymentFailedException;
use App\Services\v1\Payment\TransactionFailedException;
use App\Services\v1\Payment\TrustapPaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

// TODO: have to be logged in and have a data in UserTrustapMetadata
class TrustapController extends Controller
{
    public $trustapPaymentGateway;

    public function __construct(TrustapPaymentGateway $trustapPaymentGateway)
    {
        $this->trustapPaymentGateway = $trustapPaymentGateway;
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
                'role' => ['required', 'string', 'in:buyer,seller'],
                'description' => ['required', 'string', 'max:255'],
            ]);

            $redirectUrl = null;
            DB::transaction(function () use(&$redirectUrl, $validated, $gig){
                $redirectUrl = $this->trustapPaymentGateway->createTransaction($validated, $gig);
            });
            Log::info($redirectUrl);
            return redirect()->away($redirectUrl);
        } catch (TransactionFailedException $e) {
            return $this->apiError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error creating transaction: '.$e->getMessage());
            return $this->apiError('An error occurred while creating the transaction.');
        }
    }

    public function paymentCallback(Request $request)
    {
        // dd($request->all());
        try {
            $result = $this->trustapPaymentGateway->paymentSuccess($request->all());
            if (! $result) {
                return $this->apiError('Payment processing failed.');
            }
            return $this->apiSuccess('Payment processed successfully.');
        } catch (PaymentFailedException $e) {
            return $this->apiError($e);
        } catch (\Exception $e) {
            Log::error('An error occurred during payment callback: '.$e->getMessage());
            return $this->apiError('An error occurred during payment processing.');
        }
    }

    public function sellerAcceptDeposit(Request $request, EntityTrustapTransaction $entityTrustapTransaction)
    {
        try {
            $response = $this->trustapPaymentGateway->sellerAcceptDeposit($entityTrustapTransaction);

            return $this->respondOK('Deposit accepted successfully.', 200);
        } catch (PaymentFailedException $e) {
            return $this->respondError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('SellerAcceptDeposit: '.$e->getMessage());

            return $this->respondError('An error occurred while accepting the deposit: ');
        }
    }

    public function buyerConfirmsHandover(Request $request, EntityTrustapTransaction $entityTrustapTransaction)
    {
        try {
            $response = $this->trustapPaymentGateway->buyerConfirmsHandover($entityTrustapTransaction);

            return $this->respondOk('Handover confirmed successfully.', 200);
        } catch (PaymentFailedException $e) {
            return $this->respondError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('BuyerConfirmsHandover: '.$e->getMessage());

            return $this->respondError('An error occurred while confirming the handover: ');
        }
    }

    public function buyerSubmitComplaint(Request $request, EntityTrustapTransaction $entityTrustapTransaction)
    {
        try {
            $validated = $request->validate([
                'complaint' => ['required', 'string', 'max:500'],
            ]);

            $response = $this->trustapPaymentGateway->buyerSubmitComplaint($entityTrustapTransaction, $validated['complaint']);

            return $this->respondOk('Complaint submitted successfully.', 200);
        } catch (PaymentFailedException $e) {
            return $this->respondError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('Buyer Submit Complaint: '.$e->getMessage());

            return $this->respondError('An error occurred while submitting the complaint: ');
        }
    }

    public function sellerClaimsPayout(Request $request, EntityTrustapTransaction $entityTrustapTransaction)
    {
        try {
            $response = $this->trustapPaymentGateway->sellerClaimsPayout($entityTrustapTransaction);

            return $this->respondOk('Payout claimed successfully.', 200);
        } catch (PaymentFailedException $e) {
            return $this->respondError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('SellerClaimsPayout: '.$e->getMessage());

            return $this->respondError('An error occurred while claiming the payout: ');
        }
    }

    function testResponse(){
        return $this->apiSuccess('payment has been completed');
    }
}
