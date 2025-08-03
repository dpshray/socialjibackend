<?php

namespace App\Http\Controllers\Api\v1\Review;

use App\Exceptions\ForbiddenItemAccessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Review\SubReviewRequest;
use App\Http\Resources\Review\SubReviewResource;
use App\Models\Review;
use App\Models\SubReview;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubReviewController extends Controller
{
    use ResponseTrait, PaginationTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Review $review)
    {
        $per_page = $request->query('per_page', 10);
        $sub_reviews = $review->subReviews()->with(['reviewer.media'])->paginate($per_page);
        $sub_reviews = $this->setupPagination($sub_reviews, fn($data) => SubReviewResource::collection($data));
        return $this->apiSuccess('list of sub reviews', $sub_reviews);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubReviewRequest $request, Review $review)
    {
        $data = $request->merge(['user_id' => Auth::id()])->all();
        $review->subReviews()->create($data);
        return $this->apiSuccess('sub review added');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubReviewRequest $request, SubReview $sub_review)
    {
        $this->isSubReviewOwner($sub_review);
        $sub_review->update($request->only('comment'));
        return $this->apiSuccess('sub review updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubReview $sub_review)
    {
        $this->isSubReviewOwner($sub_review);
        $sub_review->delete();
        return $this->apiSuccess('sub review deleted');
    }
    
    private function isSubReviewOwner(SubReview $sub_review)
    {
        if ($sub_review->user_id != Auth::id()) {
            throw new ForbiddenItemAccessException('You have no access to this review');
        }
    }
}
