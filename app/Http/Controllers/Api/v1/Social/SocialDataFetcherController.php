<?php

namespace App\Http\Controllers\Api\v1\Social;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Models\SocialProfile;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class SocialDataFetcherController extends Controller
{
    use ResponseTrait;

    public function redirectToFacebook()
    {
        $redirect_url = Socialite::driver('facebook')
            ->scopes(['pages_show_list', 'pages_read_engagement'])
            ->redirect();

        $user_id = Auth::id();
        $token = Crypt::encryptString($user_id);

        $redirect_url = $redirect_url.'?token='.$token;
        return $this->apiSuccess('redirect_url', compact('redirect_url'));
    }

    public function handleFacebookCallback(Request $request)
    {
        try {
            $token = $request->query('token');
            $user_id = Crypt::decryptString($token);
            $user = User::findOrFail($user_id);#just to verify

            $facebookUser = Socialite::driver('facebook')->stateless()->user();
            // Save or update user
            // $user = Auth::user();

            $metadata = [
                'name' => $facebookUser->getName(),
                'id' => $facebookUser->getId(),
                'token' => $facebookUser->token,
            ];
            /* $user = User::updateOrCreate(
                ['email' => $facebookUser->getEmail()], // match by email
                ['metadata' => $metadata]
            ); */
            $fn_row_id = $this->getFbRowId();
            // $user_id = Auth::id();
            $social_profile = SocialProfile::updateOrCreate([
                'social_email' => $facebookUser->getEmail(),
                'social_site_id' => $fn_row_id,
                'user_id' => $user->id 
            ],[
                'metadata' => $metadata,
            ]);
            // $user_id = $social_profile->user_id;
            /* $fb_social_profile = SocialProfile::where([
                ['social_email', $facebookUser->getEmail()]
            ]);
            if ($fb_social_profile->exists()) {
                $fb_social_profile->update([
                    'metadata' => $metadata
                ]);
            } */
            // Auth::login($user);
            // $user_id = Crypt::encryptString($user_id);
            // $redirect_url = route('facebook.pages', ['token' => $user_id]);
            // return $this->apiSuccess('redirect_url', compact('redirect_url'));
            return redirect()->route('facebook.pages', ['token' => $token]);
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Facebook login failed: ' . $e->getMessage());
        }
    }

    public function getFacebookPages(Request $request, $token)
    {
        $user_id = Crypt::decryptString($token);
        $user = User::findOrFail($user_id);
        $token = $user->metadata->token;
        // $token = Auth::user()->facebook_token;

        try {
            $response = Http::get("https://graph.facebook.com/v19.0/me/accounts", [
                'access_token' => $token,
            ]);

            $pages = $response->json()['data'] ?? [];

            $pagesWithFollowers = [];

            foreach ($pages as $page) {
                $pageAccessToken = $page['access_token'];
                $pageId = $page['id'];

                $followersResponse = Http::get("https://graph.facebook.com/v19.0/$pageId", [
                    'fields' => 'name,followers_count',
                    'access_token' => $pageAccessToken,
                ]);

                $pageData = $followersResponse->json();
                $pagesWithFollowers[] = [
                    'name' => $pageData['name'] ?? 'Unknown',
                    'followers_count' => $pageData['followers_count'] ?? 'N/A',
                ];
            }
            $sum_of_followers = collect($followersResponse)->sum(function($item){
                return is_numeric($item['followers_count']) ? $item['followers_count'] : 0; 
            });

            $fb_table_id = $this->getFbRowId();
            SocialProfile::where([
                ['user_id', $user->id],
                ['social_site_id', $fb_table_id]
            ])
            ->update(['follower_count' => $sum_of_followers]);
            // return view('facebook.pages', compact('pagesWithFollowers'));
            return response()->json($pagesWithFollowers);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to fetch Facebook pages: ' . $e->getMessage());
        }
    }

    private function getFbRowId(){
        return DB::table('social_sites')
            ->where('name', strtolower(Constants::FACEBOOK_LABEL))
            ->first()
            ->id;
    }
}
