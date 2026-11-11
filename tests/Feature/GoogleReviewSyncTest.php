<?php

namespace Tests\Feature;

use App\Models\GoogleBusinessConnection;
use App\Models\Testimonial;
use App\Services\GoogleReviewClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleReviewSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_imports_new_reviews_and_keeps_an_unpublished_one_hidden(): void
    {
        config([
            'services.google.client_id' => 'client',
            'services.google.client_secret' => 'secret',
        ]);

        GoogleBusinessConnection::query()->create([
            'refresh_token' => 'refresh',
            'account_resource' => 'accounts/1',
            'location_resource' => 'locations/2',
            'connected_at' => now(),
        ]);

        Testimonial::query()->create([
            'customer_name' => 'Old name',
            'rating' => 3,
            'review' => 'Old comment',
            'source' => 'Google',
            'google_review_id' => 'existing',
            'is_published' => false,
            'is_demo' => false,
            'sort_order' => 1,
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'token']),
            'https://mybusiness.googleapis.com/*' => Http::response([
                'reviews' => [
                    [
                        'reviewId' => 'existing',
                        'reviewer' => ['displayName' => 'Ada'],
                        'starRating' => 'FIVE',
                        'comment' => 'Spotless kitchen',
                        'createTime' => '2026-01-02T10:00:00Z',
                    ],
                    [
                        'reviewId' => 'new',
                        'reviewer' => ['displayName' => 'Sam'],
                        'starRating' => 'FOUR',
                        'comment' => 'On time and careful',
                        'createTime' => '2026-02-02T10:00:00Z',
                    ],
                    [
                        'reviewId' => 'blank',
                        'reviewer' => ['displayName' => 'Bea'],
                        'starRating' => 'FIVE',
                        'comment' => '',
                    ],
                ],
            ]),
        ]);

        $result = app(GoogleReviewClient::class)->sync();

        $this->assertSame(['created' => 1, 'updated' => 1, 'skipped' => 1], $result);

        $existing = Testimonial::query()->where('google_review_id', 'existing')->first();
        $this->assertFalse($existing->is_published);
        $this->assertSame('Ada', $existing->customer_name);
        $this->assertSame(5, $existing->rating);
        $this->assertSame('Spotless kitchen', $existing->review);

        $created = Testimonial::query()->where('google_review_id', 'new')->first();
        $this->assertTrue($created->is_published);
        $this->assertFalse($created->is_demo);
        $this->assertSame('Sam', $created->customer_name);
    }

    public function test_reviews_stay_manual_when_google_is_not_configured(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $this->assertFalse(app(GoogleReviewClient::class)->isConfigured());
    }
}
