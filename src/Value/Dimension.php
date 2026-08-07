<?php

declare(strict_types=1);

namespace Atelier\Layout\Value;

use Atelier\Layout\Exception\InvalidArgumentException;

final readonly class Dimension
{
    private function __construct(
        public DimensionKind $kind,
        public float $value = 0.0,
    ) {
        if ($value < 0.0) {
            throw new InvalidArgumentException(sprintf('Dimension value must not be negative, got %s.', $value));
        }
    }

    public static function fixed(float $value): self
    {
        return new self(DimensionKind::Fixed, $value);
    }

    public static function auto(): self
    {
        return new self(DimensionKind::Auto);
    }

    public static function stretch(float $flex = 1.0): self
    {
        if ($flex <= 0.0) {
            throw new InvalidArgumentException(sprintf('Stretch flex must be positive, got %s.', $flex));
        }

        return new self(DimensionKind::Stretch, $flex);
    }

    public static function minContent(): self
    {
        return new self(DimensionKind::MinContent);
    }

    public static function maxContent(): self
    {
        return new self(DimensionKind::MaxContent);
    }
}
