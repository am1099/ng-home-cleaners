<?php

namespace Tests\Unit\Pricing;

use App\Pricing\PriceRange;
use PHPUnit\Framework\TestCase;

class PriceRangeTest extends TestCase
{
    public function test_adds_fixed_amounts_in_pence(): void
    {
        $range = new PriceRange(20000, 23000);

        $result = $range->addFixed(4500, 5500);

        $this->assertSame(24500, $result->minPence);
        $this->assertSame(28500, $result->maxPence);
    }

    public function test_enforces_regular_minimum_floor(): void
    {
        $range = new PriceRange(5000, 6000);

        [, $after] = $range->enforceMinimum(5500, 6500);

        $this->assertSame(5500, $after->minPence);
        $this->assertSame(6500, $after->maxPence);
    }

    public function test_rounds_to_nearest_step(): void
    {
        $range = new PriceRange(7125, 8075);

        $rounded = $range->round(500);

        $this->assertSame(7000, $rounded->minPence);
        $this->assertSame(8000, $rounded->maxPence);
    }
}
