<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $row = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();
        
        if (empty($row)) {
            return $this->apiError('email does not exists',404);
        }
        $timestamp = $row->created_at;
        $time = Carbon::parse($timestamp);
        if ($time->addMinutes(Constants::PASSWORD_RESET_TOKEN_EXPIRE_MIN)->isPast()) {
            return $this->apiError('Token has already been expired');
        }
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => $request->password,
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );
        if ($status == Password::PASSWORD_RESET) {
            return $this->apiSuccess('Password has been reset successfully');
        }else if($status == Password::INVALID_TOKEN){
            return $this->apiError('Token does not match',500);
        }else if($status == Password::INVALID_USER){
            return $this->apiError('Invalid user',500);
        }
        // return $status == Password::PASSWORD_RESET
        //             ? $this->respondOk(__($status))
        //             : $this->respondError(__($status));
    }
}
