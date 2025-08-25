<?php

namespace App\Http\Controllers\Api\v1\Influencer;

use App\Http\Resources\Campaign\CampaignResource;
use App\Http\Controllers\Controller;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use App\Models\Campaign;

class InfluencerCampaignController extends Controller
{
    use ResponseTrait, PaginationTrait;

    public function campaignList(Request $request){
        $per_page = $request->query('per_page',5);
        $pagination = Campaign::with(['tags','media'])
            ->when($request->filled('search'), fn($qry) => $qry->where('title', 'like', '%'.$request->search.'%'))
            ->latest()
            ->paginate($per_page);
        $data = $this->setupPagination($pagination, fn($item) => CampaignResource::collection($item))->data;
        return $this->apiSuccess("list of available campaigns", $data);
    }
}
