<?php

namespace App\Http\Controllers;

use App\Http\Requests\Influencer\Tag\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Support\Facades\Log;
use Throwable;

class TagController extends Controller
{
    public function index()
    {
        return $this->respondSuccess(Tag::paginate(10)->toArray());
    }

    public function store(StoreTagRequest $request)
    {
        try {
            $validated = $request->validated();

            $tag = Tag::firstOrCreate([
                'name' => $validated['name'],
                'user_id' => auth()->id(),
            ]);

            return $this->respondSuccess(['tag' => $tag], 'Tag created successfully.', 201);
        } catch (Throwable $th) {
            Log::error('Error while saving tag. ERROR: '.$th->getMessage());

            return $this->respondError('Unable to save tag.');
        }

    }

    public function show(Tag $tag)
    {
        return $this->respondSuccess($tag->toArray());
    }

    public function search($keyword)
    {
        $tags = Tag::where('name', 'like', "%$keyword%")->paginate(5)->toArray();

        return $this->respondSuccess($tags);
    }
}
