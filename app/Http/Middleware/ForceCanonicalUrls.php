<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCanonicalUrls
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $requestUri = (string) $request->server->get('REQUEST_URI', $request->getRequestUri());
        $path = (string) (parse_url($requestUri, PHP_URL_PATH) ?: '/');
        $query = parse_url($requestUri, PHP_URL_QUERY);

        if ($path !== '/' && str_ends_with($path, '/')) {
            $target = rtrim($path, '/');

            return redirect()->to($target.($query ? '?'.$query : ''), 301);
        }

        return $next($request);
    }
}
