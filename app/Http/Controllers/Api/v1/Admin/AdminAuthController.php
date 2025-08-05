<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminAuthController extends Controller
{
    public function login(Request $request){
        $form_data = $request->validate([
            'email' => ['required','exists:users,email'],
            'password' => ['required']
        ]);
        $row = User::role(Constants::ROLE_ADMIN)
                                ->where('email', $form_data['email'])
                                ->first();
        if (empty($row)) {
            return $this->apiError('email does not exists', 404);
        }elseif (!$row->hasVerifiedEmail()) {
            return $this->apiError('email is not verified');
        }
        if (! $token = JWTAuth::attempt($form_data)) {
            return $this->apiError('Invalid Credentials');
        }
        $user = Auth::user();
        $role = $user->getRoleNames()->first();
        $user = new UserResource($user);
        return $this->apiSuccess('logged in successfull', compact('user', 'role', 'token'));
    }
}
