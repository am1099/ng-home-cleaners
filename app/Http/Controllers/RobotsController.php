<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $sitemap = url('/sitemap.xml');

        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /livewire/',
            'Disallow: /get-a-quote/confirmation',
            'Disallow: /get-a-quote/confirmation/',
            '',
            'Sitemap: '.$sitemap,
            '',
        ]);

        return response($body, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
