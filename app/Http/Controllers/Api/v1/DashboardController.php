<?php

namespace App\Http\Controllers\Api\v1;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Social\MinimalSocailProfileRequest;
use App\Http\Resources\Client\Insight\TopBrandInfluencerResource;
use App\Http\Resources\Social\MinimalSocialProfileRequest;
use App\Http\Resources\Social\MinimalSocialProfileResource;
use App\Models\User;
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

        $data = [
            'social_followers' => $social_followers,
            'top_influencer_with_max_followers_count' => $top_influencer_with_max_followers,
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

        $data = [
            'social_followers' => $social_followers,
            'top_brand_with_max_followers' => $top_brand_with_max_followers,
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
