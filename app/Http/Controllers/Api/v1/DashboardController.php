<?php

namespace App\Http\Controllers\Api\v1;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Social\MinimalSocailProfileRequest;
use App\Http\Resources\Client\Insight\TopBrandInfluencerResource;
use App\Http\Resources\Social\MinimalSocialProfileRequest;
use App\Http\Resources\Social\MinimalSocialProfileResource;
use App\Models\Gig;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function influencer(Request $request)
    {
        $influencer = Auth::user();
        $social_followers = $this->getSocialMediaFollowers();
        // $per_page = $request->query('per_page', 10);
        $top_influencer_with_max_followers = User::role(Constants::ROLE_INFLUENCER)
            ->with(['socialProfiles', 'media', 'gigReviews'])
            ->withSum('socialProfiles', 'follower_count')
            ->orderByDesc('social_profiles_sum_follower_count')
            ->take(5)
            ->get();
        $top_influencer_with_max_followers  = TopBrandInfluencerResource::collection($top_influencer_with_max_followers);
        $total_gigs = $influencer->gigs()->count();
        $total_reviews_received = $influencer->gigs()->withCount('reviews')->get()->sum('reviews_count');
        $total_reviews_given = $this->getTotalReviewGiven();
        $total_bids = DB::Table('bids')->where('bidder_id', Auth::id())->count();

        #graph
        $year = now()->format('Y');
        $no_of_gigs_published_on_current_year = DB::table('gigs')
            ->selectRaw('MONTH(published_at) as month, COUNT(*) as total')
            ->whereYear('published_at', $year)
            ->groupByRaw('MONTH(published_at)')
            ->orderByRaw('MONTH(published_at)')
            ->whereNull('deleted_at')
            ->get();

        $campaign_published_on_current_year =  DB::table('campaigns')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->whereNull('deleted_at')
            ->get();

        /* $campaign_published_on_current_year = $no_of_gigs_published_on_current_year = [
            [
                "month" => 1,
                "total" => 5000
            ],
            [
                "month" => 2,
                "total" => 150000
            ],
            [
                "month" => 3,
                "total" => 20000
            ],
            [
                "month" => 4,
                "total" => 35000
            ],
            [
                "month" => 5,
                "total" => 80000
            ],
            [
                "month" => 6,
                "total" => 65000
            ],
            [
                "month" => 7,
                "total" => 45000
            ],
            [
                "month" => 8,
                "total" => 87000
            ]
        ]; */

        $data = [
            'social_followers' => $social_followers,
            'top_influencer_with_max_followers_count' => $top_influencer_with_max_followers,
            'no_of_gigs_published_on_current_year' => $no_of_gigs_published_on_current_year,
            'campaign_published_on_current_year' => $campaign_published_on_current_year,
            'total_gigs_count' => $total_gigs,
            'total_reviews_received_from_gigs_count' => $total_reviews_received,
            'total_reviews_given_count' => $total_reviews_given,
            'total_bidded_on_campaign_count' => $total_bids,

        ];

        return $this->apiSuccess('influencer dashboard data', $data);
    }

    public function brand(Request $request){
        $brand = Auth::user();

        $top_brand_with_max_followers = User::role(Constants::ROLE_BRAND)
            ->with(['socialProfiles', 'media', 'brandCategory'])
            ->withSum('socialProfiles', 'follower_count')
            ->orderByDesc('social_profiles_sum_follower_count')
            ->take(5)
            ->get();
        $total_campaigns_added_count = $brand->brandCampaigns()->count();
        $social_followers = $this->getSocialMediaFollowers();
        $total_reviews_given = $this->getTotalReviewGiven();
        $total_bidders_from_all_campaign_count = $brand->brandCampaigns()->withCount('bids')->get()->sum('bids_count');
        $total_user_rated_my_brand = $brand->brandRatings()->count();
        
        $top_brand_with_max_followers = TopBrandInfluencerResource::collection($top_brand_with_max_followers);


        $year = now()->format('Y');
        $campaign_published_on_current_year =  DB::table('campaigns')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->whereNull('deleted_at')
            ->where('brand_id', $brand->id)
            ->get();

        $buyer_transactions = $brand->userTrustapMetadata->buyerTransactions;
        $rawCounts = $buyer_transactions
            ->filter(fn($t) => Carbon::parse($t->complaintPeriodDeadline)->year == $year) // only 2025
            ->groupBy(fn($t) => Carbon::parse($t->complaintPeriodDeadline)->month) // group by month number (1–12)
            ->map->count(); // [8 => 2, 9 => 3]

        $currentMonth = Carbon::now()->month;
        $gig_brought_on_current_year = collect(range(1, $currentMonth))->map(function ($month) use ($rawCounts) {
            return [
                'Month' => $month,
                'gig_sold' => $rawCounts[$month] ?? 0
            ];
        })->values()->toArray();
        
        $data = [
            'social_followers' => $social_followers,
            'top_brand_with_max_followers' => $top_brand_with_max_followers,
            'own_campaign_published_on_current_year' => $campaign_published_on_current_year,
            'gig_brought_on_current_year' => $gig_brought_on_current_year,
            'total_campaigns_added_count' => $total_campaigns_added_count,
            'total_reviews_given_count' => $total_reviews_given,
            'total_bidders_on_campaign_count' => $total_bidders_from_all_campaign_count,
            'total_user_rated_my_brand' => $total_user_rated_my_brand
        ];
        
        return $this->apiSuccess('brand dashboard data', $data);
    }

    private function getSocialMediaFollowers(){
        $social_profiles = Auth::user()->socialProfiles;
        $social_profiles->load('socialSite');
        return MinimalSocialProfileResource::collection($social_profiles);
        // return $social_profiles;
    }

    public function getTotalReviewGiven(){
        return $total_reviews_given = DB::Table('reviews')->where('user_id', Auth::id())->count();
    }
}
