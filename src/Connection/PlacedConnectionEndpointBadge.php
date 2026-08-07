<?php

declare(strict_types=1);

namespace Atelier\Layout\Connection;

use Atelier\Layout\Geometry\Point;
use Atelier\Layout\Geometry\Rect;

final readonly class PlacedConnectionEndpointBadge
{
    public function __construct(
        public Rect $frame,
        public Point $anchor,
        public int $segmentIndex,
        public ConnectionEndpointBadgePlacement $placement,
    ) {
    }
}
