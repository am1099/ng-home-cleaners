<?php

namespace App\Services\Analytics;

use App\Pricing\Money;
use Illuminate\Support\Carbon;

final class ReportingPeriod
{
    public const TIMEZONE = 'Europe/London';

    public function __construct(
        public readonly int $days,
        public readonly Carbon $start,
        public readonly Carbon $end,
        public readonly Carbon $previousStart,
        public readonly Carbon $previousEnd,
    ) {}

    public static function make(int $days): self
    {
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;
        $end = now()->timezone(self::TIMEZONE)->endOfDay();
        $start = $end->copy()->startOfDay()->subDays($days - 1);
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->startOfDay()->subDays($days - 1);

        return new self($days, $start, $end, $previousStart, $previousEnd);
    }

    public function label(): string
    {
        return $this->start->format('j M').' – '.$this->end->format('j M Y');
    }

    public function previousLabel(): string
    {
        return $this->previousStart->format('j M').' – '.$this->previousEnd->format('j M Y');
    }

    public function countChange(int $current, int $previous, string $singular, string $plural): string
    {
        $delta = $current - $previous;

        if ($delta === 0) {
            return 'Same as '.$this->previousLabel();
        }

        $word = abs($delta) === 1 ? $singular : $plural;

        return $delta > 0
            ? $delta.' more '.$word
            : abs($delta).' fewer '.$word;
    }

    public function moneyChange(int $currentPence, int $previousPence): string
    {
        $delta = $currentPence - $previousPence;

        if ($delta === 0) {
            return 'Same as '.$this->previousLabel();
        }

        $amount = Money::formatPenceExact(abs($delta));

        return $delta > 0
            ? $amount.' more received'
            : $amount.' less received';
    }
}
