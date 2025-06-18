<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

class PasswordResetLinkController extends Controller
{
    use ResponseTrait;

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);
        $user = DB::table('users')->where('email', $request->email)->first();
        $user_id = $user->id;
        $token = strtoupper(str()->random(8));

        $link_expires_minute   = Constants::PASSWORD_RESET_TOKEN_EXPIRE_MIN;
        $token_expires_at = now()->addMinutes($link_expires_minute)->format('Y-m-d H:i:s');
        
        DB::table('password_reset_tokens')->updateOrInsert([
            'email' => $request->email
        ],[
            'created_at' => now(), 'token' => bcrypt($token)
        ]);
        
        Mail::send('email.password-reset', ['token' => $token, 'token_expires_at' => $token_expires_at, 'user' => $user], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password');
        });
        return $this->apiSuccess('a mail has been sent to your email');
        #this logic does not works in API
        // $status = Password::sendResetLink(
        //     $request->only('email')
        // );
        // if ($status === Password::RESET_LINK_SENT) {
        //     return $this->apiSuccess('An email has been sent to your mail');
        // } else{
        //     return $this->apiError($status);
        // }
        // return $status === Password::RESET_LINK_SENT
        //         ? $this->respondOk(__($status))
        //         : $this->respondError(__($status));
    }
}
