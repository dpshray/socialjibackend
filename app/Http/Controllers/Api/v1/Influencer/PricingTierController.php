<?php

namespace App\Http\Controllers\Api\v1\Influencer;

use App\Http\Controllers\Controller;
use App\Models\PricingTier;
use Illuminate\Http\Request;

class PricingTierController extends Controller
{
    public function index()
    {
        $pricingTiers = PricingTier::select('id','name','label')->get();
        return $this->apiSuccess('list of available pricing tiers', $pricingTiers);
    }
}
