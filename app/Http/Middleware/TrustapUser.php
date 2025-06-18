<?php

namespace App\Http\Middleware;

use App\Models\UserTrustapMetadata;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustapUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $user = \App\Models\User::find(15);
        // auth()->login($user);

        $trustapUser = UserTrustapMetadata::whereUserId(auth()->id())->first();

        if (! $trustapUser) {
            return response()->json([
                'error' => 'Trustap user not found.',
            ], 404);
        }

        return $next($request);
    }
}
