<?php

namespace App\Http\Controllers\Api\v1\Client;

use App\Constants\Constants;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Explorer\BrandResource;
use App\Http\Resources\Client\Explorer\InfluencerResource;
use App\Http\Resources\Client\Explorer\TopSaleResource;
use App\Models\EntityTrustapTransaction;
use App\Models\User;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientDashboardController extends Controller
{
    use PaginationTrait, ResponseTrait;

    public function influencerExplorer(Request $request){
        $per_page = $request->query('per_page',10);
        $influencer = User::role(Constants::ROLE_INFLUENCER)
                        ->with(['reviews', 'socialProfiles.socialSite','media', 'gigs'])
                        ->withCount(['gigs'])
                        ->take($per_page)
                        ->get();
        $influencer = InfluencerResource::collection($influencer);
        return $this->apiSuccess('influencer explorer data', $influencer);
    }

    public function brandExplorer(Request $request){
        $per_page = $request->query('per_page', 10);
        $brand = User::role(Constants::ROLE_BRAND)
                    ->with(['media', 'socialProfiles.socialSite', 'brandCategory', 'brandRatings'])
                    ->take($per_page)
                    ->get();
        $brand = BrandResource::collection($brand);

        // $brand = $brand->paginate($per_page);
        // $brand = $this->setupPagination($brand, fn($brand) => BrandResource::collection($brand))->data;
        return $this->apiSuccess('brand explorer data', $brand);
    }

    public function topSalesExplorer(Request $request){
        $per_page = $request->query('per_page', 10);
        $top_sales = EntityTrustapTransaction::join('gigs', 'gigs.id', '=', 'entity_trustap_transactions.gig_id')
                    ->selectRaw('COUNT(entity_trustap_transactions.gig_id) as total_sold, entity_trustap_transactions.gig_id, gigs.title')
                    ->groupBy('entity_trustap_transactions.gig_id', 'gigs.title')
                    ->with(['gig' => ['media', 'gig_pricing']])
                    ->where(function($qry){
                        return $qry->where('entity_trustap_transactions.status', PaymentStatusEnum::HANDOVERED->value);
                    })
                    ->whereDate('complaintPeriodDeadline','<=', now())
                    ->orderBy('total_sold', 'DESC')
                    ->take($per_page)
                    ->get();
        $top_sales = TopSaleResource::collection($top_sales);
        return $this->apiSuccess('top sales explorer data', $top_sales);

    }
}
