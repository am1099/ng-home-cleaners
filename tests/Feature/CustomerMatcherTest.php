<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Services\CustomerMatcher;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMatcherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_exact_email_matches_existing_customer(): void
    {
        $existing = Customer::query()->create([
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone_normalized' => '447503651476',
            'phone_display' => '07503 651476',
            'email' => 'alex@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '1 Test Street',
            'city' => 'Nottingham',
        ]);

        $match = app(CustomerMatcher::class)->findMatch('Alex@Example.com', '07503999999');

        $this->assertTrue($existing->is($match));
    }

    public function test_phone_matches_when_email_missing_on_one_side(): void
    {
        $existing = Customer::query()->create([
            'first_name' => 'Sam',
            'last_name' => 'Patel',
            'phone_normalized' => '07503651476',
            'phone_display' => '07503 651476',
            'email' => null,
            'postcode' => 'NG2 2BB',
            'address_line1' => '2 Test Lane',
            'city' => 'Nottingham',
        ]);

        $match = app(CustomerMatcher::class)->findMatch('sam@example.com', '07503651476');

        $this->assertTrue($existing->is($match));
    }

    public function test_conflicting_email_on_same_phone_does_not_merge(): void
    {
        Customer::query()->create([
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'phone_normalized' => '447503651476',
            'phone_display' => '07503 651476',
            'email' => 'alex@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '1 Test Street',
            'city' => 'Nottingham',
        ]);

        $match = app(CustomerMatcher::class)->findMatch('other@example.com', '07503651476');

        $this->assertNull($match);
    }
}
