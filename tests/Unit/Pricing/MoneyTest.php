<?php

namespace Tests\Unit\Pricing;

use App\Pricing\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_round_to_nearest_five_pounds(): void
    {
        $this->assertSame(7500, Money::roundToNearest(7695, 500));
        $this->assertSame(7500, Money::roundToNearest(7500, 500));
        $this->assertSame(8000, Money::roundToNearest(7750, 500));
    }

    public function test_apply_percent_increase_without_float_drift(): void
    {
        $this->assertSame(21000, Money::applyPercentIncrease(20000, '5'));
        $this->assertSame(23940, Money::applyPercentIncrease(21000, '14'));
    }

    public function test_apply_percent_decrease(): void
    {
        $this->assertSame(7695, Money::applyPercentDecrease(8100, '5'));
    }

    public function test_percent_of_span(): void
    {
        $this->assertSame(8100, Money::percentOfSpan(7500, 8500, '60'));
    }

    public function test_format_pence_range(): void
    {
        $this->assertSame('£75', Money::formatPence(7500));
        $this->assertSame('£280–£335', Money::formatPenceRange(28000, 33500));
    }
}
