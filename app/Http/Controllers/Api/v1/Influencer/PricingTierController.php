<?php

namespace App\Http\Controllers\Api\v1\Influencer;

use App\Http\Controllers\Controller;
use App\Models\PricingTier;
use Illuminate\Http\Request;

class PricingTierController extends Controller
{
    public function index()
    {
        $pricingTiers = PricingTier::all()->toArray();

        return $this->respondSuccess($pricingTiers);
    }
}
