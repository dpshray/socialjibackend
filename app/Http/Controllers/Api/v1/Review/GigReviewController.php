<?php

namespace App\Http\Controllers\Api\v1\Review;

use App\Exceptions\ForbiddenItemAccessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Review\ReviewRequest;
use App\Http\Resources\Review\RevCollection;
use App\Http\Resources\Review\ReviewCollection;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Gig;
use App\Models\Review;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GigReviewController extends Controller
{
    use PaginationTrait;

    public function storeGigReview(ReviewRequest $request, Gig $gig)
    {
        $validated = $request->validated();
        $validated['gig_id'] = $gig->id;
        $comment = Auth::user()->brandReviews()->create($validated);
        return $this->apiSuccess('gig reviewed successfully', $comment);
    }

    public function fetchGigReviews(Request $request, Gig $gig)
    {
        $per_page = $request->query('per_page');
        $paginated_reviews = $gig->reviews()->with(['user.media', 'helpfuls'])->latest()->paginate($per_page);
        $reviews = $paginated_reviews->items();
        $reviews = $this->setupPagination($paginated_reviews, fn($review) => ReviewResource::collection($review))->data;
        return $this->apiSuccess("gig title: {$gig->title} reviews", $reviews);
    }

    public function gigReviewUpdater(ReviewRequest $request, Review $review)
    {
        $this->isReviewOwner($review);
        $data = $request->validated();
        $review->update($data);
        return $this->apiSuccess('review updated', $data);
    }

    public function gigReviewRemover(Request $request, Review $review)
    {
        $this->isReviewOwner($review);
        $review->delete();
        return $this->apiSuccess('review deleted');
    }

    public function markReviewHelpful(Request $request, Review $review)
    {
        $form_data = $request->validate([
            'vote' => 'required|between:0,1'
        ]);
        $review->helpfuls()->updateOrCreate([
            'user_id' => Auth::id(),
            'review_id' => $review->id
        ], [
            'vote' => $form_data['vote']
        ]);
        $status = ($form_data['vote'] == 1) ? 'up voted' : 'down voted';
        return $this->apiSuccess("review {$status}");
    }

    private function isReviewOwner(Review $review)
    {
        if ($review->user_id != Auth::id()) {
            throw new ForbiddenItemAccessException('You have no access to this review');
        }
    }
}
