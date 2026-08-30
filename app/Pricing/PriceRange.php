<?php

namespace App\Pricing;

use InvalidArgumentException;

final readonly class PriceRange
{
    public function __construct(
        public int $minPence,
        public int $maxPence,
    ) {
        if ($minPence < 0 || $maxPence < 0) {
            throw new InvalidArgumentException('Price ranges cannot be negative.');
        }

        if ($minPence > $maxPence) {
            throw new InvalidArgumentException('Minimum price cannot exceed maximum price.');
        }
    }

    public static function single(int $pence): self
    {
        return new self($pence, $pence);
    }

    public static function zero(): self
    {
        return new self(0, 0);
    }

    public function add(self $other): self
    {
        return new self(
            $this->minPence + $other->minPence,
            $this->maxPence + $other->maxPence,
        );
    }

    public function addFixed(int $minPence, int $maxPence): self
    {
        return $this->add(new self($minPence, $maxPence));
    }

    /**
     * @return array{0: self, 1: self} [before, after]
     */
    public function multiply(string $multiplier): array
    {
        $before = clone $this;

        return [
            $before,
            new self(
                Money::multiply($this->minPence, $multiplier),
                Money::multiply($this->maxPence, $multiplier),
            ),
        ];
    }

    /**
     * @return array{0: self, 1: self} [before, after]
     */
    public function applyPercentIncrease(string $percent): array
    {
        $before = clone $this;

        return [
            $before,
            new self(
                Money::applyPercentIncrease($this->minPence, $percent),
                Money::applyPercentIncrease($this->maxPence, $percent),
            ),
        ];
    }

    /**
     * @return array{0: self, 1: self}
     */
    public function enforceMinimum(int $minFloorPence, int $maxFloorPence): array
    {
        $before = clone $this;

        return [
            $before,
            new self(
                max($this->minPence, $minFloorPence),
                max($this->maxPence, $maxFloorPence),
            ),
        ];
    }

    /**
     * @return array{0: self, 1: self}
     */
    public function narrow(int $signalCount, string $percentPerSignal, string $maxPercent): array
    {
        $before = clone $this;

        if ($signalCount <= 0 || $this->minPence === $this->maxPence) {
            return [$before, $before];
        }

        $narrowPercent = min(
            (float) $percentPerSignal * $signalCount,
            (float) $maxPercent,
        );

        $factor = bcsub('1', bcdiv((string) $narrowPercent, '100', 6), 6);
        $mid = (int) round(($this->minPence + $this->maxPence) / 2);
        $half = Money::multiply((int) round(($this->maxPence - $this->minPence) / 2), $factor);

        return [
            $before,
            new self($mid - $half, $mid + $half),
        ];
    }

    public function round(int $stepPence): self
    {
        return new self(
            Money::roundToNearest($this->minPence, $stepPence),
            Money::roundToNearest($this->maxPence, $stepPence),
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
