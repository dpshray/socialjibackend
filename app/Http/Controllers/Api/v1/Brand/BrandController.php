<?php

namespace App\Http\Controllers\Api\v1\Brand;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tag\TagCollection;
use App\Http\Resources\UserCollection;
use App\Models\Gig;
use App\Models\Tag;
use App\Models\User;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller {
    use PaginationTrait;
    
    public function creatorSearch(Request $request){
        $keyword = $request->query('name');
        $per_page = $request->query('per_page');
        $creators = User::select('id','nick_name','about')
                        ->with(['socialProfiles'])
                        ->whereRelation('roles','name',Constants::ROLE_INFLUENCER)
                        ->where('nick_name','like', '%'.$keyword.'%')
                        ->paginate($per_page);

        $creators = $this->setupPagination($creators, UserCollection::class)->data;
        return $this->apiSuccess('list of creators ', $creators);
    }

    public function searchTag(Request $request){
        $keyword = $request->query('name');
        $per_page = $request->query('per_page');
        $tags = Tag::select('id', 'name')
            ->where('name', 'like', '%' . $keyword . '%')
            ->paginate($per_page);
        $tags = $this->setupPagination($tags, TagCollection::class)->data;
        return $this->apiSuccess('list of tags', $tags);
    }
}
