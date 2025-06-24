<?php

namespace App\Http\Controllers;

use App\Http\Requests\Influencer\Tag\StoreTagRequest;
use App\Http\Resources\Tag\TagCollection;
use App\Models\Tag;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class TagController extends Controller
{
    use PaginationTrait;

    public function index()
    {
        $tags = Tag::creator()->select('id','name')->paginate();
        $tags = $this->setupPagination($tags, TagCollection::class)->data;
        return $this->apiSuccess('list of available tags', $tags);
    }

    public function store(StoreTagRequest $request)
    {
        $validated = $request->validated();
        try {
            $tag = Tag::firstOrCreate([
                'name' => $validated['name'],
                'user_id' => Auth::id(),
            ]);
            return $this->apiSuccess('Tag created successfully.', $tag);
        } catch (Throwable $th) {
            Log::error('Error while saving tag. ERROR: '.$th->getMessage());
            return $this->apiError('Unable to save tag.');
        }

    }

    public function show(Tag $tag)
    {
        return $this->respondSuccess($tag->toArray());
    }

    public function search(Request $request)
    {
        $keyword = $request->query('name');
        $tags = Tag::creator()->where('name', 'like', "%$keyword%")->paginate();
        $tags = $this->setupPagination($tags, TagCollection::class)->data;
        return $this->apiSuccess('search result for tag name '.$keyword, $tags);
    }
}
