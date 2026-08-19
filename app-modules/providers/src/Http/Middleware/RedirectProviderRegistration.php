<?php

namespace Nucleus\Providers\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * If a freshly-registered user has the provider role but no profile yet,
 * push them to the provider registration / credential entry flow before
 * the rest of the app is reachable.
 *
 * Forks that don't need this gating can omit the alias from kernel
 * middleware groups.
 */
class RedirectProviderRegistration
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('provider')) {
            return $next($request);
        }

        if ($request->is('provider/register*') || $request->is('auth/*') || $request->is('logout')) {
            return $next($request);
        }

        if (! $user->providerProfile) {
            return redirect('/provider/register');
        }

        return $next($request);
    }
}
