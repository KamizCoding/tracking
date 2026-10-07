<?php

namespace App\Http\Middleware;

use App\Models\LinkDomain;
use Closure;
use Illuminate\Http\Request;

class IsCustomDomain
{
    public function handle(Request $request, Closure $next)
    {
        $hostname = $request->getHost();

        // Check if this is a verified custom domain
        $customDomain = LinkDomain::where('domain', $hostname)
            ->where('status', 'verified')
            ->where('verified', true)
            ->first();

        if (! $customDomain) {
            // Not a custom domain - this route shouldn't handle this request
            // Pass through to let Laravel try other routes
            // We set a request attribute to signal this is not a custom domain
            $request->attributes->set('is_custom_domain', false);

            return $next($request);
        }

        // This is a verified custom domain - proceed to tracking controller
        $request->attributes->set('is_custom_domain', true);
        $request->attributes->set('custom_domain', $customDomain);

        return $next($request);
    }
}
