<?php

namespace Nucleus\Kyc\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nucleus\Kyc\Models\KycVerification;
use Symfony\Component\HttpFoundation\Response;

class RequireKyc
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        // Allow the KYC page itself and logout through
        if ($request->is('kyc/*') || $request->is('kyc')) {
            return $next($request);
        }

        $verification = KycVerification::where('user_id', auth()->id())->first();

        // If no record yet, create one and send to KYC
        if (! $verification) {
            KycVerification::create([
                'user_id' => auth()->id(),
                'status'  => 'pending',
            ]);
            return redirect('/kyc/verify');
        }

        // Failed KYC — only allow the verify page
        if ($verification->isFailed()) {
            return redirect('/kyc/verify');
        }

        // Not yet verified — send to KYC
        if (! $verification->isVerified()) {
            return redirect('/kyc/verify');
        }

        return $next($request);
    }
}
