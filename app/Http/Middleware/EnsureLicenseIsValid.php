<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\LicenseChecker;
use Illuminate\Http\Request;

class EnsureLicenseIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only run license validation on login and login-with-ad routes (system login)
        // if ($request->is('api/login') || $request->is('api/login-with-ad')) {
        //     $licenseChecker = app(LicenseChecker::class);

        //     // 1. Basic license validity (Signature, Fingerprint, Expiry)
        //     if (!$licenseChecker->isValid()) {
        //         return response()->json([
        //             'error' => 'LICENSE_INVALID',
        //             'message' => 'License is missing, invalid, or expired',
        //         ], 403);
        //     }

        //     // 2. Concurrent user restriction
        //     if (auth()->check() && !$licenseChecker->checkConcurrentUsers()) {
        //         return response()->json([
        //             'error' => 'CONCURRENT_USER_LIMIT',
        //             'message' => 'concurrent user limit reached try agin',
        //         ], 403);
        //     }
        // }

        return $next($request);
    }
}
