<?php

namespace App\Pricing;

final class Money
{
    public static function add(int $a, int $b): int
    {
        return $a + $b;
    }

    public static function subtract(int $a, int $b): int
    {
        return $a - $b;
    }

    public static function multiply(int $pence, string $multiplier): int
    {
        return (int) round((float) bcmul((string) $pence, $multiplier, 4));
    }

    public static function applyPercentIncrease(int $pence, string $percent): int
    {
        $factor = bcadd('1', bcdiv($percent, '100', 6), 6);

        return self::multiply($pence, $factor);
    }

    public static function applyPercentDecrease(int $pence, string $percent): int
    {
        $factor = bcsub('1', bcdiv($percent, '100', 6), 6);

        return self::multiply($pence, $factor);
    }

    public static function percentOfSpan(int $minPence, int $maxPence, string $percent): int
    {
        $span = $maxPence - $minPence;

        return $minPence + self::multiply($span, bcdiv($percent, '100', 6));
    }

    public static function roundToNearest(int $pence, int $stepPence): int
    {
        if ($stepPence <= 0) {
            return $pence;
        }

        return (int) (round($pence / $stepPence) * $stepPence);
    }

    public static function formatPence(int $pence): string
    {
        return '£'.number_format($pence / 100, 0);
    }

    public static function formatPenceExact(int $pence): string
    {
        return '£'.number_format($pence / 100, 2);
    }

    public static function formatPenceRange(int $minPence, int $maxPence): string
    {
        if ($minPence === $maxPence) {
            return self::formatPence($minPence);
        }

        return self::formatPence($minPence).'–'.self::formatPence($maxPence);
    }
}
