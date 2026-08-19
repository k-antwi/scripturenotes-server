<?php

namespace Nucleus\Kyc\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user has permission to review KYC verifications.
 * Checks for the 'view_kyc_verification' Spatie permission.
 */
class KycReviewer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->can('view_kyc_verification')) {
            abort(403, 'You do not have permission to access KYC documents.');
        }

        return $next($request);
    }
}
