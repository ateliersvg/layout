<?php

declare(strict_types=1);

namespace Atelier\Layout\Band;

use Atelier\Layout\Geometry\Rect;

final readonly class PlacedEdgeBand
{
    public function __construct(
        public string $id,
        public Rect $frame,
        public Rect $bandFrame,
        public Rect $contentFrame,
        public bool $overflowY,
        public bool $bandAtTop,
    ) {
    }
}
