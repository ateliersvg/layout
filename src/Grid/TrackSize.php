<?php

declare(strict_types=1);

namespace Atelier\Layout\Grid;

use Atelier\Layout\Exception\InvalidArgumentException;

final readonly class TrackSize
{
    private function __construct(
        public TrackSizeKind $kind,
        public float $value = 0.0,
    ) {
        if ($value < 0.0) {
            throw new InvalidArgumentException(sprintf('Track size value must not be negative, got %s.', $value));
        }
    }

    public static function fixed(float $px): self
    {
        return new self(TrackSizeKind::Fixed, $px);
    }

    public static function fr(float $fraction = 1.0): self
    {
        if ($fraction <= 0.0) {
            throw new InvalidArgumentException(sprintf('Track fraction must be positive, got %s.', $fraction));
        }

        return new self(TrackSizeKind::Fraction, $fraction);
    }

    public static function auto(): self
    {
        return new self(TrackSizeKind::Auto);
    }
}
