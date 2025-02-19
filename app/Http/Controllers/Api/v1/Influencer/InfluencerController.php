<?php

namespace App\Http\Controllers\Api\v1\Influencer;

use App\Http\Controllers\Controller;
use App\Models\User;

class InfluencerController extends Controller
{
    public function findInfluencers($keyword)
    {
        $users = User::role('influencer')
            ->active()
            ->verifiedEmail()
            ->where('first_name', 'like', "%$keyword%")
            ->orWhere('middle_name', 'like', "%$keyword%")
            ->orWhere('middle_name', 'like', "%$keyword%")
            ->orWhere('last_name', 'like', "%$keyword%")
            ->get()
            ->toArray();

        return $this->respondSuccess($users);
    }
}
