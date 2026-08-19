<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Wave\Setting;

class LoginRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect('/login');
        }

        foreach (config('auth-redirects.roles', []) as $role => $path) {
            if ($user->hasRole($role)) {
                return redirect($path);
            }
        }

        if (! Setting::get('onboarding.require_onboarding', '1')) {
            return redirect(config('onboarding.completion_redirect', '/dashboard'));
        }

        return redirect(config('auth-redirects.default', '/onboarding'));
    }
}
