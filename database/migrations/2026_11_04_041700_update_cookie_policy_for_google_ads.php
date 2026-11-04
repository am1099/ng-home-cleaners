<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('legal_pages')->where('slug', 'cookies')->first();

        if ($page === null || ! str_contains((string) $page->content, 'We do not use advertising cookies.')) {
            return;
        }

        DB::table('legal_pages')->where('slug', 'cookies')->update([
            'seo_description' => 'Essential cookies and optional Google Ads tags used by the NG Home Cleaners website.',
            'content' => str_replace(
                'We do not use advertising cookies.',
                'If you accept cookies, we also use Google Ads tags to measure visits from our adverts and record conversions, such as an estimate request. Those advertising cookies stay off until you accept. If you choose essential only, the Google tag remains on the page but advertising storage stays denied.',
                (string) $page->content,
            ),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $page = DB::table('legal_pages')->where('slug', 'cookies')->first();

        if ($page === null || ! str_contains((string) $page->content, 'If you accept cookies, we also use Google Ads tags')) {
            return;
        }

        DB::table('legal_pages')->where('slug', 'cookies')->update([
            'seo_description' => 'Essential cookies used by the NG Home Cleaners website. We do not use advertising cookies.',
            'content' => str_replace(
                'If you accept cookies, we also use Google Ads tags to measure visits from our adverts and record conversions, such as an estimate request. Those advertising cookies stay off until you accept. If you choose essential only, the Google tag remains on the page but advertising storage stays denied.',
                'We do not use advertising cookies.',
                (string) $page->content,
            ),
            'updated_at' => now(),
        ]);
    }
};
