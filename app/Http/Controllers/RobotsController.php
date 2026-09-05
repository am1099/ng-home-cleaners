<?php

namespace App\Http\Controllers;

use App\Services\SeoService;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(SeoService $seo): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /livewire',
            'Disallow: /livewire/',
            'Disallow: /get-a-quote/confirmation',
            'Disallow: /get-a-quote/confirmation/',
            '',
            'Sitemap: '.$seo->absoluteRoute('sitemap'),
            '',
        ]);

        return response($body, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
