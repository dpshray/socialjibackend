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
use Illuminate\Support\Facades\Log;

class SocialDataFetcherController extends Controller
{
    use ResponseTrait;

    public function redirectToFacebook()
    {
        // $user = User::find(3); #test
        $user = Auth::user();
        if (empty($user)) {
            return $this->apiError('Token not valid', 401);
        }
        $token = Crypt::encryptString($user->id);
        // dd(base64_encode($token));
        return Socialite::driver('facebook')
                ->stateless()
                ->scopes(['pages_show_list', 'pages_read_engagement'])
                ->with(['state' => base64_encode($token)])
                ->redirect();
    }

    public function facebookCallback(Request $request)
    {
        try {
            $token = base64_decode($request->query('state'));
            $user_id = Crypt::decryptString($token);
            $user = User::findOrFail($user_id);#test

            $facebookUser = Socialite::driver('facebook')->stateless()->user();

            $metadata = [
                'name' => $facebookUser->getName(),
                'id' => $facebookUser->getId(),
                'token' => $facebookUser->token,
            ];

            $fn_row_id = $this->getFbRowId();

            $social_profile = SocialProfile::updateOrCreate([
                'social_email' => $facebookUser->getEmail(),
                'social_site_id' => $fn_row_id,
                'user_id' => $user->id,
            ],[
                'metadata' => ($metadata),
            ]);
            return redirect()->route('facebook.pages', ['token' => $token, 'access_token' => $facebookUser->token]);
        } catch (\Exception $e) {
            Log::info($e);
            return redirect('/')->with('error', 'Facebook login failed: ' . $e->getMessage());
        }
    }

    public function getFacebookPages(Request $request, $token, $access_token)
    {
        $user_id = Crypt::decryptString($token);
        $user = User::findOrFail($user_id);
        // $token = $user->metadata['token'];
        // $token = Auth::user()->facebook_token;

        try {
            $response = Http::get("https://graph.facebook.com/v19.0/me/accounts", [
                'access_token' => $access_token,
            ]);

            $pages = $response->json()['data'] ?? [];

            $pagesWithFollowers = [];
            // Log::info($pages);
            $followersResponse = null;
            foreach ($pages as $page) {
                $pageAccessToken = $page['access_token'];
                $pageId = $page['id'];

                $followersResponse = Http::get("https://graph.facebook.com/v19.0/$pageId", [
                    'fields' => 'name,followers_count',
                    'access_token' => $pageAccessToken,
                ])->json();
                Log::info($followersResponse);
                $pagesWithFollowers[] = [
                    'name' => $followersResponse['name'] ?? 'Unknown',
                    'followers_count' => $followersResponse['followers_count'] ?? 0,
                ];
            }
            $sum_of_followers = collect($pagesWithFollowers)->sum(function($item){
                return is_numeric($item['followers_count']) ? $item['followers_count'] : 0; 
            });

            $fb_table_id = $this->getFbRowId();
            SocialProfile::where([
                ['user_id', $user->id],
                ['social_site_id', $fb_table_id]
            ])
            ->update(['follower_count' => $sum_of_followers]);
            return redirect()->away(config('services.facebook.final_redirect_url'));
            // return view('facebook.pages', compact('pagesWithFollowers'));
            // return response()->json($pagesWithFollowers);
        } catch (\Exception $e) {
            Log::info($e);
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
