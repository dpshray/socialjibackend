<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke($id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $user->getKey(), (string) $id)) {
            return false;
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            return false;
        }

        // $request->fulfill();
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new Verified($user));
        }

        return redirect(env('APP_FRONTEND_URL'));
    }
}
