<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanonicalHost
{
    /**
     * Redirect to APP_URL host when request uses the "other" local host
     * so the session cookie is always sent (localhost and 127.0.0.1 are different origins for cookies).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appUrl = config('app.url');
        if (! $appUrl) {
            return $next($request);
        }

        $canonicalHost = parse_url($appUrl, PHP_URL_HOST);
        $canonicalPort = parse_url($appUrl, PHP_URL_PORT);
        $requestHost = $request->getHost();
        $requestPort = $request->getPort();

        $port = $canonicalPort ?: ($request->getScheme() === 'https' ? 443 : 80);
        $requestPortNormalized = $requestPort ?: ($request->getScheme() === 'https' ? 443 : 80);

        $isSameHost = strtolower($requestHost) === strtolower($canonicalHost)
            && (int) $requestPortNormalized === (int) $port;

        if ($isSameHost) {
            return $next($request);
        }

        // Redirect localhost <-> 127.0.0.1 so session cookie works consistently
        $localHosts = ['localhost', '127.0.0.1', '::1'];
        $requestIsLocal = in_array(strtolower($requestHost), $localHosts, true);
        $canonicalIsLocal = in_array(strtolower($canonicalHost), $localHosts, true);

        if ($requestIsLocal && $canonicalIsLocal) {
            $scheme = $request->getScheme();
            $portPart = ($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80)
                ? ''
                : ':' . $port;
            $canonicalUrl = $scheme . '://' . $canonicalHost . $portPart . $request->getRequestUri();
            return redirect()->away($canonicalUrl, 302);
        }

        return $next($request);
    }
}
