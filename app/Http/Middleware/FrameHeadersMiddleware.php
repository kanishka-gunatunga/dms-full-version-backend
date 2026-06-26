<?php

namespace App\Http\Middleware;

use Closure;

class FrameHeadersMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Remove restrictive iframe header
        $response->headers->remove('X-Frame-Options');

        $csp = "
            default-src 'self';
            base-uri 'self';
            object-src 'none';
            media-src 'self' http: https:;

            connect-src 'self'
                http://localhost:3000
                http://localhost:8000
                http://127.0.0.1:3000
                http://127.0.0.1:8000
                http://172.20.122.45:3000
                http://172.20.122.45:8000
                https://cloudflareinsights.com
                https://login.microsoftonline.com
                https://graph.microsoft.com
                https://login.live.com
                https://sts.windows.net;

            img-src 'self'
                data:
                blob:
                http://localhost:3000
                http://localhost:8000
                http://127.0.0.1:3000
                http://127.0.0.1:8000
                http://172.20.122.45:3000
                http://172.20.122.45:8000
                https:
                https://*.microsoftonline.com;

            script-src 'self'
                'unsafe-inline'
                'unsafe-eval'
                https://static.cloudflareinsights.com
                https://alcdn.msauth.net
                https://res.cdn.office.net;

            style-src 'self'
                'unsafe-inline'
                https:;

            font-src 'self'
                https:
                data:;

            frame-src 'self'
                http://localhost:3000
                http://localhost:8000
                http://127.0.0.1:3000
                http://127.0.0.1:8000
                http://172.20.122.45:3000
                http://172.20.122.45:8000
                https://view.officeapps.live.com
                https://login.microsoftonline.com
                https://login.live.com;

            frame-ancestors
                'self'
                http://localhost:3000
                http://127.0.0.1:3000
                http://172.20.122.45:3000
                ;
        ";

        $response->headers->set(
            'Content-Security-Policy',
            preg_replace('/\s+/', ' ', trim($csp))
        );

        return $response;
    }
}