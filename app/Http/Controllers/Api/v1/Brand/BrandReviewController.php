<?php

namespace App\Http\Controllers\Api\v1\Brand;

use App\Exceptions\ForbiddenItemAccessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\Review\ReviewCollection;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Gig;
use App\Models\Review;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use League\CommonMark\Extension\CommonMark\Parser\Block\ThematicBreakStartParser;

class BrandReviewController extends Controller
{
    use PaginationTrait;

    public function storeGigReview(ReviewRequest $request, Gig $gig)
    {
        $validated = $request->validated();
        $validated['gig_id'] = $gig->id;
        $comment = Auth::user()->brandReviews()->create($validated);
        $comment = new ReviewResource($comment);
        return $this->apiSuccess('gig reviewed successfully', $comment);
    }

    public function fetchGigReviews(Request $request, Gig $gig) {
        $per_page = $request->query('per_page');
        // $gig->loadMissing(['reviews.user.media', 'reviews.helpfuls']);
        $paginated_reviews = $gig->reviews()->with(['user.media', 'helpfuls'])->latest()->paginate($per_page);
        $reviews = $paginated_reviews->items();
        $reviews = $this->setupPagination($paginated_reviews, ReviewCollection::class)->data;
        return $this->apiSuccess("gig title: {$gig->title} reviews", $reviews);
    }

    public function gigReviewUpdater(ReviewRequest $request, Review $review) {}

    public function gigReviewRemover(Request $request, Review $review) {}

    private function isReviewOwner(Review $review)
    {
        if ($review->isNot(Auth::user())) {
            throw new ForbiddenItemAccessException('You have no access to this review');
        }
    }
}
