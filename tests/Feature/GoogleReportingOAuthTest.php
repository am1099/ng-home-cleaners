<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleReportingOAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporting_callback_is_a_staff_route(): void
    {
        $this->get('/google-reporting/callback')->assertRedirect('/admin/login');

        $this->assertStringEndsWith(
            '/google-reporting/callback',
            (string) config('services.google.reporting_redirect'),
        );
    }
}
