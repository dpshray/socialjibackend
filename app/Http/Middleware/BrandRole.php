<?php

namespace App\Http\Middleware;

use App\Constants\Constants;
use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BrandRole
{
    use ResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->user() || ! auth()->user()->hasRole(Constants::ROLE_BRAND)) {
            return $this->apiError('Only users with the brand role are allowed to make this request.', 401);
        }

        return $next($request);
    }
}
