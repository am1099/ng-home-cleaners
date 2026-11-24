<?php

namespace App\Http\Middleware;

use App\Services\AdAttribution;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureAdAttribution
{
    public function __construct(private readonly AdAttribution $attribution) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->attribution->capture($request);

        return $next($request);
    }
}
