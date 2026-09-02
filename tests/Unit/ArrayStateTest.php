<?php

namespace Tests\Unit;

use App\Support\ArrayState;
use PHPUnit\Framework\TestCase;

class ArrayStateTest extends TestCase
{
    public function test_it_normalizes_json_strings(): void
    {
        $this->assertSame(['a', 'b'], ArrayState::normalize('["a","b"]'));
    }

    public function test_it_wraps_scalar_strings(): void
    {
        $this->assertSame(['pet_hair'], ArrayState::normalize('pet_hair'));
    }

    public function test_it_returns_empty_array_for_null(): void
    {
        $this->assertSame([], ArrayState::normalize(null));
    }
}
