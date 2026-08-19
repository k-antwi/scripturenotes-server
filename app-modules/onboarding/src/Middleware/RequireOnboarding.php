<?php

namespace Nucleus\Onboarding\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nucleus\Onboarding\Models\OnboardingSubmission;
use Symfony\Component\HttpFoundation\Response;
use Wave\Setting;

class RequireOnboarding
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        if (! Setting::get('onboarding.require_onboarding', '1')) {
            if ($request->is('onboarding') || $request->is('onboarding/*')) {
                return redirect(config('onboarding.completion_redirect', '/dashboard'));
            }

            return $next($request);
        }

        if ($request->is('onboarding/*') || $request->is('onboarding')
            || $request->is('auth/*') || $request->is('logout')
        ) {
            return $next($request);
        }

        $submission = OnboardingSubmission::where('user_id', auth()->id())->first();

        // No submission yet — send to onboarding
        if (! $submission) {
            return redirect('/onboarding');
        }

        // Submission exists but not yet submitted — send to onboarding to resume
        if (! $submission->isSubmitted()) {
            return redirect('/onboarding');
        }

        // Onboarding complete — allow through
        return $next($request);
    }
}
