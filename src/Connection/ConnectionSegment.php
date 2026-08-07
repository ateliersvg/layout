<?php

declare(strict_types=1);

namespace Atelier\Layout\Connection;

use Atelier\Layout\Axis;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Point;

final readonly class ConnectionSegment
{
    public function __construct(
        public int $index,
        public Point $start,
        public Point $end,
        public Axis $axis,
    ) {
        if ($index < 0) {
            throw new InvalidArgumentException('Connection segment index must not be negative.');
        }
    }

    public function isHorizontal(): bool
    {
        return Axis::Horizontal === $this->axis;
    }

    public function isVertical(): bool
    {
        return Axis::Vertical === $this->axis;
    }
}
