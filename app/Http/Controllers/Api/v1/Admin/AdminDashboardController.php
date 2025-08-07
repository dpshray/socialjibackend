<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Constants\Constants;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\EntityTrustapTransaction;
use App\Models\Gig;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    use ResponseTrait;
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $brand_count = User::role(Constants::ROLE_BRAND)->count();
        $influencer_count = User::role(Constants::ROLE_INFLUENCER)->count();
        $gig_count = Gig::count();
        $succesful_payment = EntityTrustapTransaction::paymentOverallComplete()->count(); 
        return $this->apiSuccess('admin dashboard', compact('brand_count','influencer_count','gig_count','succesful_payment'));
    }
}
