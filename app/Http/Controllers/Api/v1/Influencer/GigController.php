<?php

namespace App\Http\Controllers\Api\v1\Influencer;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Influencer\Gig\StoreGigRequest;
use App\Http\Resources\Gig\GigCollection;
use App\Http\Resources\Gig\GigResource;
use App\Http\Resources\Search\GigSearchResource;
use App\Models\Gig;
use App\Services\GigService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Traits\PaginationTrait;

class GigController extends Controller
{
    use PaginationTrait;

    private $user = null;
    public function __construct()
    {
        $this->user = Auth::user();
    }
    /**
     * for influencer
     */
    public function index()
    {
        $gigs = Gig::select('id','user_id','title','description')
                    ->with(['media','user' => ['media', 'brandReviews']])
                    ->creator()
                    ->latest()
                    ->paginate();
        $gigs = $this->setupPagination($gigs, GigCollection::class)->data;
        return $this->apiSuccess('list of available gigs of user : '.$this->user->nick_name, $gigs);
    }

    public function store(StoreGigRequest $request)
    {
        try {
            DB::transaction(function () use($request){
                $validated = $request->validated();
                app(GigService::class)->setFormData($validated)->store()->upsertPricingAndTag();
            });
            return $this->apiSuccess('Gig created successfully.');

        } catch (Throwable $th) {
            Log::error('Error while saving gig. ERROR: '.$th->getMessage());
            return $this->apiError($th->getMessage());
        }
    }

    public function show(Gig $gig)
    {
        /* if (! $gig->isCreator()) {
            return $this->apiError('Forbidden', 403);
        } */

        try {
            $gig = Gig::with([
                'reviews' => fn($q) => $q->latest()->take(5),
                'reviews.user.media',
                'reviews.helpfuls',
                'media',
                'gig_pricing',
                'tags',
            ])->find($gig->id);

            $data = new GigResource($gig);
            return $this->apiSuccess('gig detail', $data);
        } catch (Throwable $th) {
            Log::error("Error while showing gig id: {$gig->id}. ERROR: " . $th->getMessage());
            return $this->apiError('Unable to show gig.');
        }
    }


    public function update(StoreGigRequest $request, Gig $gig)
    {
        if (! $gig->isCreator()) {
            return $this->apiError('Forbidden', 403);
        }
        try {
            $validated = $request->validated();
            app(GigService::class)->setFormData($validated)->update($gig)->upsertPricingAndTag();
            return $this->apiSuccess('Gig updated successfully.');
        } catch (Throwable $th) {
            Log::error("Error while updating gig id:  $gig->id(). ERROR: ".$th->getMessage());
            return $this->apiError('Unable to update gig.');
        }
    }

    public function destroy(Gig $gig)
    {
        if (! $gig->isCreator()) {
            return $this->apiError('Forbidden', 403);
        }

        try {
            $gig->delete();

            return $this->apiSuccess('Gig deleted successfully.');
        } catch (Throwable $th) {
            Log::error("Error while deleting gig id:  $gig->id(). ERROR: ".$th->getMessage());

            return $this->apiError('Unable to delete gig.', 409);
        }
    }

    public function search(Request $request)
    {
        $gig_name = $request->query('name');
        $tag_id= $request->query('tag_id');
        $from_price= $request->query('from_price');
        $to_price= $request->query('to_price');
        $per_page= $request->query('per_page');
        $gigs = Gig::with([
                'tags:id,name', 
                'user:id,nick_name,first_name,middle_name,last_name' => ['brandReviews','media'],
                'media'
                ])
                ->when(!collect(Auth::user()->roles)->contains('name', Constants::ROLE_BRAND), function($qry){
                    return $qry->where('user_id', Auth::id());
                })
                ->select('id','user_id','title','description')
                ->where('title', 'like', "%$gig_name%")
                ->when($tag_id, function($qry,$val){
                    $qry->whereRelation('tags','tags.id',$val);
                })
                ->when(($from_price || $to_price), function($qry) use($from_price,$to_price){
                    if ($from_price > 0 && $to_price > 0) {
                        $qry->whereHas('gig_pricing', fn($qry) => $qry->whereBetween('price', [$from_price,$to_price]));
                    } else if($from_price == null && $to_price > 0){
                        $qry->whereHas('gig_pricing', fn($qry) => $qry->where('price', '<=', $to_price));
                    } else{
                        $qry->whereHas('gig_pricing', fn($qry) => $qry->where('price', '>', $from_price));
                    }
                })
                ->paginate($per_page);
                // return GigSearchResource::collection($gigs);
        $gigs = $this->setupPagination($gigs, fn($items) => GigSearchResource::collection($items))->data;
        // $gigs = $gigs->items();
        
        return $this->apiSuccess('gig search results',$gigs);
    }

    /* public function searchByTag($tag)
    {
        $gigs = Gig::select('id','title')
                ->whereHas('tags', function ($query) use ($tag) {
                    $query->where('tags.name', 'like', "%$tag%");
                })->paginate();
        $gigs = $gigs->items();
        return $this->apiSuccess('gig list based on tag name(s)',$gigs);
    } */
}
