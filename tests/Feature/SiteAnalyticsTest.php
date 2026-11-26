<?php

namespace Tests\Feature;

use App\Models\SiteEvent;
use App\Models\User;
use App\Services\AdAttribution;
use App\Services\SiteEventRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_views_are_ignored_until_cookies_are_accepted(): void
    {
        $this->postJson('/analytics/events', [
            'event' => SiteEventRecorder::PAGE_VIEW,
            'path' => '/',
        ])->assertNoContent();

        $this->assertDatabaseCount('site_events', 0);
    }

    public function test_whatsapp_open_is_recorded_with_its_page(): void
    {
        $this->postJson('/analytics/events', [
            'event' => SiteEventRecorder::WHATSAPP_OPENED,
            'path' => '/get-a-quote?bedrooms=1',
            'ads' => true,
        ])->assertNoContent();

        $event = SiteEvent::query()->first();

        $this->assertNotNull($event);
        $this->assertSame('/get-a-quote', $event->path);
        $this->assertSame('Google Ads', $event->source);
        $this->assertTrue($event->from_ads);
        $this->assertNull($event->visitor_key);
    }

    public function test_consented_visit_counts_a_visitor(): void
    {
        $this->withCredentials()
            ->disableCookieEncryption()
            ->withCookie(AdAttribution::CONSENT_COOKIE, 'accepted')
            ->postJson('/analytics/events', [
                'event' => SiteEventRecorder::PAGE_VIEW,
                'path' => '/services',
                'visitor' => '11111111-1111-4111-8111-111111111111',
                'utm_source' => 'google',
            ])->assertNoContent();

        $this->assertDatabaseHas('site_events', [
            'event' => SiteEventRecorder::PAGE_VIEW,
            'path' => '/services',
            'source' => 'google',
        ]);
    }

    public function test_analytics_page_is_available_to_staff(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/analytics')
            ->assertOk()
            ->assertSee('WhatsApp opened')
            ->assertSee('Google Business Profile')
            ->assertSee('Age and gender are not collected');
    }
}
