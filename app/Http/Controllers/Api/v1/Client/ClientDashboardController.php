<?php
namespace App\Http\Controllers\Api\v1\Client;

use App\Constants\Constants;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Explorer\BrandResource;
use App\Http\Resources\Client\Explorer\InfluencerResource;
use App\Http\Resources\Client\Explorer\TopSaleResource;
use App\Http\Resources\Client\Insight\TopBrandInfluencerResource;
use App\Http\Resources\UserResource;
use App\Models\BrandCategory;
use App\Models\EntityTrustapTransaction;
use App\Models\Gig;
use App\Models\Review;
use App\Models\User;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientDashboardController extends Controller
{
    use PaginationTrait, ResponseTrait;

    /*=============================================
    =                  EXPLORER PART              =
    =============================================*/
    /**
     * list of influencer based on their profile(followers)
     */
    public function influencerExplorer(Request $request)
    {
        $per_page = $request->query('per_page', 10);
        $influencers = User::role(Constants::ROLE_INFLUENCER)
            ->whereHas('socialProfiles')
            ->with(['gigReviews', 'socialProfiles.socialSite', 'media', 'gigs'])
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

    /**
     * list of brand based on their profile(followers)
    */
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


    /*=============================================
    =                  INSIGHT PART               =
    =============================================*/
    public function fetchTopBrands(Request $request){
        $per_page = $request->query('per_page',5);

        $top_brand_with_max_followers = User::role(Constants::ROLE_BRAND)
            ->with(['socialProfiles','media','brandCategory'])
            ->withSum('socialProfiles', 'follower_count')
            ->orderByDesc('social_profiles_sum_follower_count')
            ->take($per_page)
            ->get();
        $users = TopBrandInfluencerResource::collection($top_brand_with_max_followers);
        return $this->apiSuccess('top brands with max followers', $users);
    }

    public function fetchTopInfluencers(Request $request){
        $per_page = $request->query('per_page',10);

        $top_influencer_with_max_followers = User::role(Constants::ROLE_INFLUENCER)
            ->with(['socialProfiles', 'media', 'gigReviews'])
            ->withSum('socialProfiles', 'follower_count')
            ->orderByDesc('social_profiles_sum_follower_count')
            ->take($per_page)
            ->get();
        $user  = TopBrandInfluencerResource::collection($top_influencer_with_max_followers);
        return $this->apiSuccess('top influencer with max followers', $user);

    }

    public function fetchNewInfluencerRegistrations(Request $request){
        $year = now()->year;
        $new_influencer_registration_by_month = User::role(Constants::ROLE_INFLUENCER)
            ->selectRaw('MONTH(email_verified_at) as month_number, COUNT(*) as total_user')
            ->whereYear('email_verified_at', $year)
            ->groupBy(DB::raw('MONTH(email_verified_at)'))
            ->orderBy('month_number')
            ->get();
        
        return $this->apiSuccess("new influencer on each month of year : $year", $new_influencer_registration_by_month);
    }

    public function fetchNewBrandRegistrations(Request $request){
        $year = now()->year;
        $new_brand_registration_by_month = User::role(Constants::ROLE_BRAND)
            ->selectRaw('MONTH(email_verified_at) as month_number, COUNT(*) as total_user')
            ->whereYear('email_verified_at', $year)
            ->groupBy(DB::raw('MONTH(email_verified_at)'))
            ->orderBy('month_number')
            ->get();

        return $this->apiSuccess("new brand on each month of year : $year", $new_brand_registration_by_month);
    }

    public function fetchBrandsByCategory(Request $request){
        $per_page = $request->query('per_page', 0);

        $no_of_brand_based_on_category = BrandCategory::has('brand')
            ->withCount('brand')
            ->when($per_page != 0, fn($qry) => $qry->take($per_page))
            ->orderBy('brand_count','DESC')
            ->get();
        
        return $this->apiSuccess('fetch brand categories with their brand count', $no_of_brand_based_on_category);
    }

    public function insightWidgetInfo(){
        [$current_y, $current_m] = [now()->format('Y'), now()->format('m')];
        [$previous_y,$previous_m] = explode('-', now()->subMonth(1)->format('Y-m'));

        $current_month_no_of_gigs = Gig::whereYear('published_at', $current_y)
            ->whereMonth('published_at', $current_m)
            ->count();
        $previous_month_no_of_gigs = Gig::whereYear('published_at', $previous_y)
            ->whereMonth('published_at', $previous_m)
            ->count();
        $increase_on_gigs = $this->monthlyGrowthPercent($previous_month_no_of_gigs, $current_month_no_of_gigs);
        $gig = [
            'new_gig_this_month' => round($current_month_no_of_gigs,1),
            'increase_on_gigs_by_percentage' => round($increase_on_gigs,1) 
        ];

        $current_month_no_of_influencers = User::role(Constants::ROLE_INFLUENCER)
            ->whereYear('email_verified_at', $current_y)
            ->whereMonth('email_verified_at', $current_m)
            ->count();
        
        $previous_month_no_of_influencers = User::role(Constants::ROLE_INFLUENCER)
            ->whereYear('email_verified_at', $previous_y)
            ->whereMonth('email_verified_at', $previous_m)
            ->count();
        $increase_on_influencers = $this->monthlyGrowthPercent($previous_month_no_of_influencers, $current_month_no_of_influencers);
        $influencer = [
            'new_influencer_this_month' => round($current_month_no_of_influencers, 1),
            'increase_on_influencer_by_percentage' => round($increase_on_influencers,1)
        ];

        $current_month_no_of_brands = User::role(Constants::ROLE_BRAND)
            ->whereYear('email_verified_at', $current_y)
            ->whereMonth('email_verified_at', $current_m)
            ->count();

        $previous_month_no_of_brands = User::role(Constants::ROLE_BRAND)
            ->whereYear('email_verified_at', $previous_y)
            ->whereMonth('email_verified_at', $previous_m)
            ->count();
        $increase_on_brands = $this->monthlyGrowthPercent($previous_month_no_of_brands, $current_month_no_of_brands);
        $brand = [
            'new_brand_this_month' => round($current_month_no_of_brands, 1),
            'increase_on_brand_by_percentage' => round($increase_on_brands, 1)
        ];
        #----
        $current_month_no_of_reviews = Review::whereYear('created_at', $current_y)
            ->whereMonth('created_at', $current_m)
            ->count();

        $previous_month_no_of_reviews = Review::whereYear('created_at', $current_y)
            ->whereMonth('created_at', $previous_m)
            ->count();
        $increase_on_reviews = $this->monthlyGrowthPercent($previous_month_no_of_reviews, $current_month_no_of_reviews);
        $reviews = [
            'new_review_this_month' => round($current_month_no_of_reviews, 1),
            'increase_on_review_by_percentage' => round($increase_on_reviews, 1)
        ];

        return $this->apiSuccess('insight card data', compact('gig','influencer','brand','reviews'));
    }

    public function newGigsByMonth(){
        $currentYear = now()->year;
        $groupedGigs = Gig::selectRaw('MONTH(published_at) as month_number, COUNT(*) as total_gigs')
            ->whereYear('published_at', $currentYear)
            ->groupByRaw('MONTH(published_at)')
            ->orderByRaw('MONTH(published_at)')
            ->get();
        return $this->apiSuccess('gig of year : '. $currentYear, $groupedGigs);
    }

    public function monthlyGrowthPercent(int $previous_month, int $current_month){
        if ($previous_month > 0) {
            return (($current_month - $previous_month) / $previous_month) * 100;
        } else {
            return $current_month > 0 ? 100 : 0; // or "N/A"
        }
    }
}
