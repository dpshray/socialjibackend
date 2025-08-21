<?php

namespace App\Http\Controllers\Api\v1\Influencer;

use App\Exceptions\ForbiddenItemAccessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\Bid\BidStoreRequest;
use App\Models\Bid;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BidController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BidStoreRequest $request, Campaign $campaign)
    {
        $data = $request->validated();
        $data['bidder_id'] = Auth::id();
        $campaign->bids()->create($data);
        return $this->apiSuccess('Bid added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bid $bid)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bid $bid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bid $bid)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bid $bid)
    {
        $this->isOwner($bid);
        $bid->delete();
        return $this->apiSuccess('bid deleted successfully');
    }

    private function isOwner(Bid $bid){
        throw_if($bid->bidder->isNot(Auth::user()), ForbiddenItemAccessException::class);
    }
}
