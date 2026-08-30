<?php

namespace App\Http\Controllers;

use App\Services\SeoService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SeoService $seo): Response
    {
        $xml = view('seo.sitemap', [
            'entries' => $seo->sitemapEntries(),
        ])->render();

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
