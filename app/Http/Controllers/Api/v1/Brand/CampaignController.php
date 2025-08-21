<?php

namespace App\Http\Controllers\Api\v1\Brand;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\CampaignStoreRequest;
use App\Http\Resources\Campaign\CampaignResource;
use App\Models\Campaign;
use App\Traits\ResponseTrait;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ForbiddenItemAccessException;
use Illuminate\Support\Facades\Log;

class CampaignController extends Controller
{
    use ResponseTrait, PaginationTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // dd($request->all());
        $brand = Auth::user();
        $per_page = $request->query('per_page', 10);
        $pagination = $brand->brandCampaigns()
            ->with(['tags','media'])
            ->latest()
            ->paginate($per_page);
        $data = $this->setupPagination($pagination, fn($item) => CampaignResource::collection($item))->data;
        return $this->apiSuccess("brand({$brand->nick_name}) available campaign list", $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CampaignStoreRequest $request)
    {
        // Log::debug('hreer',$request->all());
        $tag_id = $request->tag_id;
        $user = Auth::user();
        if ($tag_id) {
            $tag_count = $user->tags()->whereIn('id', $tag_id)->count();
            if (count($tag_id) != $tag_count) {
                return $this->apiError('invalid tag(s)');
            }
        }
        // dd($tag_id);
        $campaign = null;
        DB::transaction(function () use($request, $user, &$campaign){
            // dd($campaign_tag);
            $campaign = $user->brandCampaigns()
                ->create($request->only(["title", "description", "categories", "eligibility", "requirement", "price"]));
            // ->tags()
            // ->createMany($campaign_tag);
            $campaign_tag = $request->collect('tag_id')->map(fn($item) => ['tag_id' => $item, 'campaign_id' => $campaign->id])->all();
            DB::table('campaign_tag')->insert($campaign_tag);  
            if ($request->hasFile('image')) {
                $campaign->addMedia($request->image)->toMediaCollection(Constants::MEDIA_CAMPAIGN);
            }
        });
        $campaign->loadMissing(['tags','media']);
        $campaign = new CampaignResource($campaign);
        return $this->apiSuccess('campaign added successfully', $campaign);
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        $this->isOwner($campaign);
        $campaign->loadMissing(['tags','media']);
        return $this->apiSuccess('Campaign detail', $campaign);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campaign $campaign)
    {
        // dd($request->all());
        $this->isOwner($campaign);

        $tag_id = $request->tag_id;
        $user = Auth::user();
        if ($tag_id) {
            $tag_count = $user->tags()->whereIn('id', $tag_id)->count();
            if (count($tag_id) != $tag_count) {
                return $this->apiError('invalid tag(s)');
            }
        }
        // dd($tag_id);
        DB::transaction(function () use($request, &$campaign){
            $campaign->update($request->only(["title", "description", "categories", "eligibility", "requirement", "price"]));
            // $campaign = $user->brandCampaigns()
            //     ->create($request->only(["title", "description", "categories", "eligibility", "requirement", "price"]));
            $campaign_tag = $request->collect('tag_id')->map(fn($item) => ['tag_id' => $item, 'campaign_id' => $campaign->id])->all();
            // $campaign->tags()->sync();
            // dd($campaign);
            // dd($campaign->tags);
            DB::table('campaign_tag')->where('campaign_id', $campaign->id)->delete();
            DB::table('campaign_tag')->insert($campaign_tag);
            if ($request->hasFile('image')) {
                $campaign->addMedia($request->image)->toMediaCollection(Constants::MEDIA_CAMPAIGN);
            }
        });
        $campaign->loadMissing(['tags','media']);
        $campaign = new CampaignResource($campaign);
        return $this->apiSuccess('campaign updated successfully', $campaign);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $this->isOwner($campaign);
        $campaign->delete();
        return $this->apiSuccess('campaign deleted');
    }

    public function tagsList(){
        $user = Auth::user();
        $tags = $user->tags()->select('id', 'name')->get();
        return $this->apiSuccess("user({$user->nick_name}) tags list", $tags);
    }

    private function isOwner(Campaign $campaign){
        throw_if($campaign->brand->isNot(Auth::user()), ForbiddenItemAccessException::class, 'You are not the owner of this resource');
    }
}
