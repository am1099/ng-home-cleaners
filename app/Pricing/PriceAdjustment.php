<?php

namespace App\Pricing;

final readonly class PriceAdjustment
{
    public function __construct(
        public int $minPence,
        public int $maxPence,
    ) {}

    public static function zero(): self
    {
        return new self(0, 0);
    }

    public static function fromRangeDelta(PriceRange $after, PriceRange $before): self
    {
        return new self(
            $after->minPence - $before->minPence,
            $after->maxPence - $before->maxPence,
        );
    }

    /**
     * @return array{min_pence: int, max_pence: int}
     */
    public function toArray(): array
    {
        return [
            'min_pence' => $this->minPence,
            'max_pence' => $this->maxPence,
        ];
    }
}
