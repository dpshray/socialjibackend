<?php

namespace App\Http\Controllers\Api\v1\Influencer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Influencer\StoreGigRequest;
use App\Models\Gig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class GigController extends Controller
{
    public function store(StoreGigRequest $request)
    {
        try {
            $validated = $request->validated();
            $pricingData = collect($request->only('pricing_tier', 'price', 'delivery_time', 'tier_description'));

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = $request->file('image');
                $path = 'influencer/gig/';
                $gigImage = uploadFile($image, $path, disk: 'public');
            }

            $gig = Gig::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'category' => $validated['category'],
                'description' => $validated['description'],
                'requirements' => json_encode($validated['requirements']),
                'features' => json_encode($validated['features']),
                'image' => $gigImage ?? null,
                'status' => $validated['status'],
                'published_at' => ! empty($validated['published']) ? Carbon::now() : null,
            ]);

            $pricingData = $pricingData->transpose()->map(fn ($data) => [
                $data[0] => [
                    'price' => $data[1],
                    'delivery_time' => $data[2],
                    'description' => $data[3],
                ],
            ]);

            $gig->gig_pricing()->sync($pricingData[0]);

            return $this->respondSuccess(['gig' => $gig], 'Gig created successfully.', 201);
        } catch (Throwable $th) {
            Log::error('Error while saving gig. ERROR: '.$th->getMessage());

            return $this->respondError('Unable to save gig.');
        }
    }

    public function show(Gig $gig)
    {
        if (! $gig->isCreator()) {
            return $this->respondForbidden();
        }

        try {
            $data = $gig->load('gig_pricing')->toArray();

            return $this->respondSuccess($data);
        } catch (Throwable $th) {
            Log::error("Error while showing gig id:  $gig->id(). ERROR: ".$th->getMessage());

            return $this->respondError('Unable to show gig.');
        }
    }

    public function update(StoreGigRequest $request, Gig $gig)
    {
        if (! $gig->isCreator()) {
            return $this->respondForbidden();
        }

        try {
            $validated = $request->validated();
            $pricingData = collect($request->only('pricing_tier', 'price', 'delivery_time', 'tier_description'));

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                if ($gig->image) {
                    deleteFile($gig->image);
                }

                $image = $request->file('image');
                $path = 'influencer/gig/';
                $gigImage = uploadFile($image, $path, disk: 'public');
            }

            $updated = $gig->update([
                'title' => $validated['title'],
                'category' => $validated['category'],
                'description' => $validated['description'],
                'requirements' => json_encode($validated['requirements']),
                'features' => json_encode($validated['features']),
                'image' => $gigImage ?? $gig->image,
                'status' => $validated['status'],
                'published_at' => ! empty($validated['published']) ? Carbon::now() : null,
            ]);

            $pricingData = $pricingData->transpose()->map(fn ($data) => [
                $data[0] => [
                    'price' => $data[1],
                    'delivery_time' => $data[2],
                    'description' => $data[3],
                ],
            ]);

            $gig->gig_pricing()->sync($pricingData[0]);

            return $this->respondSuccess(['gig' => $gig], 'Gig updated successfully.', 201);
        } catch (Throwable $th) {
            Log::error("Error while updating gig id:  $gig->id(). ERROR: ".$th->getMessage());

            return $this->respondError('Unable to update gig.');
        }
    }

    public function destroy(Gig $gig)
    {
        if (! $gig->isCreator()) {
            return $this->respondForbidden();
        }

        try {
            if ($gig->image) {
                deleteFile($gig->image, disk: 'public');
            }

            $deleted = $gig->delete();

            return $this->respondOk('Gig deleted successfully.');
        } catch (Throwable $th) {
            Log::error("Error while deleting gig id:  $gig->id(). ERROR: ".$th->getMessage());

            return $this->respondError('Unable to delete gig.');
        }
    }

    public function search($keyword)
    {
        $gigs = Gig::with('gig_pricing')->active()->where('title', 'like', "%$keyword%")->get()->toArray();

        return $this->respondSuccess($gigs);
    }

    public function index()
    {
        $gigs = Gig::with('gig_pricing')->creator()->get()->toArray();

        return $this->respondSuccess($gigs);
    }
}
