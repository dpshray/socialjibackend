<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Constants\Constants;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ListDetail\Brand\BrandListDetailResource;
use App\Http\Resources\Admin\ListDetail\Gig\GigListDetailResource;
use App\Http\Resources\Admin\ListDetail\Influencer\InfluencerListDetailResource;
use App\Http\Resources\Admin\PaymentResource;
use App\Http\Resources\Client\Explorer\BrandResource;
use App\Http\Resources\Client\Explorer\InfluencerResource;
use App\Http\Resources\Gig\GigCollection;
use App\Models\EntityTrustapTransaction;
use App\Models\Gig;
use App\Models\User;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminDataListController extends Controller
{
    use PaginationTrait, ResponseTrait;

    public function gigRecord(Request $request){
        $per_page = $request->query('per_page',10);
        $pagination = Gig::select('id','title','category','user_id','published_at')
                        ->with(['media','user:id,nick_name,first_name,middle_name,last_name' => ['media']])
                        ->withCount(['noOfGigSold','reviews'])
                        ->paginate($per_page);
        $gigs = $this->setupPagination($pagination, GigCollection::class)->data;
        return $this->apiSuccess('list of active gigs form admin', $gigs);
    }

    public function brandRecord(Request $request){
        $per_page = $request->query('per_page', 10);
        $pagination = User::role(Constants::ROLE_BRAND)
            ->with(['media','socialProfiles'])
            ->verifiedEmail()
            ->latest()
            ->paginate($per_page);
        $gigs = $this->setupPagination($pagination, fn($data) => BrandResource::collection($data))->data;
        return $this->apiSuccess('list of active brands for admin', $gigs);
    }

    public function influencerRecord(Request $request){
        $per_page = $request->query('per_page', 10);
        $pagination = User::role(Constants::ROLE_INFLUENCER)
                        ->with(['media', 'gigReviews','gigs', 'socialProfiles'])
                        ->withCount(['gigs'])
                        ->verifiedEmail()
                        ->latest()
                        ->paginate($per_page);
        $gigs = $this->setupPagination($pagination, fn($data) => InfluencerResource::collection($data))->data;
        return $this->apiSuccess('list of active influencer for admin', $gigs);
    }

    public function paymentRecord(Request $request){
        $per_page  = $request->query('per_page', 10);
        $pagination = EntityTrustapTransaction::with([
                'gig:id,title,category,published_at'=> ['media'],
                'pricing.pricingTier',
                'buyer:users.id,nick_name,first_name,middle_name,last_name' => ['media'],
                'seller:users.id,nick_name,first_name,middle_name,last_name' => ['media'],
            ])
            ->latest()
            ->paginate($per_page);

        $payments = $this->setupPagination($pagination, fn($item) => PaymentResource::collection($item))->data;
        // $top_sales = TopSaleResource::collection($top_sales);
        return $this->apiSuccess('admin payment data', $payments);
    }

    public function gigListDetail(Request $request, Gig $gig){
        $gig->load([
            'media',
            'tags:id,name',
            'gig_pricing',
            'user.media'
        ])
        ->loadCount(['reviews','noOfGigSold']);
        return $this->apiSuccess('gig details', new GigListDetailResource($gig));
    }

    public function brandListDetail(Request $request, User $user){
        if (!$user->isBrand()) {
            return $this->apiError('this user does not belong to role: brand'); 
        }
        $data = $user->load([
            'media',
            'brandCategory',
            'socialProfiles.socialSite:id,name,label', 
            'brandRatings',
            'userTrustapMetadata' => ['buyerTransactions']
            ])
            ->loadCount('userReviews');
        return $this->apiSuccess('brand details', new BrandListDetailResource($data));
    }

    public function influencerListDetail(Request $request, User $user){
        if (!$user->isInfluencer()) {
            return $this->apiError('this user does not belong to role: Influencer');
        }
        $data = $user->load([
                'media',
                'gigReviews',
                'socialProfiles.socialSite:id,name,label',
                'userTrustapMetadata' => ['sellerTransactions']
            ])
            ->loadCount(['gigs', 'gigReviews']);
        return $this->apiSuccess('influencer details', new InfluencerListDetailResource($data));
    }

    public function paymentListDetail(Request $request){

    }

}
