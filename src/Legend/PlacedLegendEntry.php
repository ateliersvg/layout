<?php

declare(strict_types=1);

namespace Atelier\Layout\Legend;

use Atelier\Layout\Geometry\Rect;

final readonly class PlacedLegendEntry
{
    public function __construct(
        public string $id,
        public Rect $frame,
        public Rect $swatchFrame,
        public Rect $labelFrame,
    ) {
    }
}
