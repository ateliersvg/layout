<?php

declare(strict_types=1);

namespace Atelier\Layout\Geometry;

final readonly class Point
{
    public function __construct(
        public float $x,
        public float $y,
    ) {
    }
}
