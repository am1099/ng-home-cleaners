<?php

use App\Http\Middleware\ForceCanonicalUrls;
use App\Services\SeoService;
use App\Services\SiteSettingsService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(ForceCanonicalUrls::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $seo = app(SeoService::class)->forNotFound();

            return response()->view('errors.404', [
                'seo' => $seo,
                'jsonLd' => [app(SeoService::class)->organizationJsonLd()],
                'settings' => app(SiteSettingsService::class)->get(),
            ], 404);
        });
    })->create();
