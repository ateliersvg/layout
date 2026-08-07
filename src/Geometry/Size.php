<?php

declare(strict_types=1);

namespace Atelier\Layout\Geometry;

use Atelier\Layout\Exception\InvalidArgumentException;

final readonly class Size
{
    public function __construct(
        public float $width,
        public float $height,
    ) {
        if ($width < 0.0 || $height < 0.0) {
            throw new InvalidArgumentException(sprintf('Size dimensions must not be negative, got %s x %s.', $width, $height));
        }
    }
}
