<?php
namespace App\Http\Controllers\Api\v1\Client;

use App\Constants\Constants;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Explorer\BrandResource;
use App\Http\Resources\Client\Explorer\InfluencerResource;
use App\Http\Resources\Client\Explorer\TopSaleResource;
use App\Http\Resources\UserResource;
use App\Models\EntityTrustapTransaction;
use App\Models\User;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientDashboardController extends Controller
{
    use PaginationTrait, ResponseTrait;

    public function influencerExplorer(Request $request)
    {
        $per_page = $request->query('per_page', 10);

        $influencers = User::role(Constants::ROLE_INFLUENCER)
            ->whereHas('socialProfiles')
            ->with(['reviews', 'socialProfiles.socialSite', 'media', 'gigs'])
            ->withCount('gigs')
            ->select('users.*')
            ->selectSub(function ($query) {
                $query->from('social_profiles')
                    ->selectRaw('COALESCE(SUM(follower_count), 0)')
                    ->whereColumn('social_profiles.user_id', 'users.id');
            }, 'total_followers')
            ->orderByDesc('total_followers')
            ->take($per_page)
            ->get();

        $influencers = InfluencerResource::collection($influencers);
        return $this->apiSuccess('influencer explorer data', $influencers);
    }

    public function brandExplorer(Request $request)
    {
        $per_page = $request->query('per_page', 10);
        $brand    = User::role(Constants::ROLE_BRAND)
            ->whereHas('socialProfiles')
            ->with(['media', 'socialProfiles.socialSite', 'brandCategory', 'brandRatings'])
            ->select('users.*')
            ->selectSub(function ($query) {
                $query->from('social_profiles')
                    ->selectRaw('COALESCE(SUM(follower_count), 0)')
                    ->whereColumn('social_profiles.user_id', 'users.id');
            }, 'total_followers')
            ->orderByDesc('total_followers')
            ->take($per_page)
            ->get();
        $brand = BrandResource::collection($brand);

        // $brand = $brand->paginate($per_page);
        // $brand = $this->setupPagination($brand, fn($brand) => BrandResource::collection($brand))->data;
        return $this->apiSuccess('brand explorer data', $brand);
    }

    public function topSalesExplorer(Request $request)
    {
        $per_page  = $request->query('per_page', 10);
        $top_sales = EntityTrustapTransaction::join('gigs', 'gigs.id', '=', 'entity_trustap_transactions.gig_id')
            ->selectRaw('COUNT(entity_trustap_transactions.gig_id) as total_sold, entity_trustap_transactions.gig_id, gigs.title')
            ->groupBy('entity_trustap_transactions.gig_id', 'gigs.title')
            ->with(['gig' => ['media', 'gig_pricing']])
            ->where(function ($qry) {
                return $qry->where('entity_trustap_transactions.status', PaymentStatusEnum::HANDOVERED->value);
            })
            ->whereDate('complaintPeriodDeadline', '<=', now())
            ->orderBy('total_sold', 'DESC')
            ->take($per_page)
            ->get();
        $top_sales = TopSaleResource::collection($top_sales);
        return $this->apiSuccess('top sales explorer data', $top_sales);
    }

    public function insightData(Request $request){
        $followers = DB::select('SELECT ss.label, sum(follower_count) AS followers_sum FROM social_profiles AS sp JOIN social_sites AS ss ON sp.social_site_id=ss.id  GROUP BY social_site_id');
        $usersByMonth = User::selectRaw('MONTH(email_verified_at) as month, COUNT(*) as total')
                        ->whereYear('email_verified_at', now()->year)
                        ->groupBy(DB::raw('MONTH(email_verified_at)'))
                        ->orderBy('month')
                        ->get();
        $top_gig_sellers = EntityTrustapTransaction::with(['gig.user.media'])
            ->select(
                'gigs.user_id',
                'users.id',
                'users.nick_name',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.email',
                'social_profiles.*',
                DB::raw('COUNT(*) as total')
            )
            ->where('entity_trustap_transactions.status', PaymentStatusEnum::HANDOVERED->value)
            ->where('complaintPeriodDeadline', '<=', now())
            ->join('gigs', 'gigs.id', '=', 'entity_trustap_transactions.gig_id')
            ->join('users', 'users.id', '=', 'gigs.user_id')
            ->join('social_profiles', 'social_profiles.user_id','users.id')
            ->groupBy('gigs.user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
        return $top_gig_sellers;
    }
}
