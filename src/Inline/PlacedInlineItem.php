<?php

declare(strict_types=1);

namespace Atelier\Layout\Inline;

use Atelier\Layout\Geometry\Rect;

final readonly class PlacedInlineItem
{
    public function __construct(
        public string $id,
        public Rect $frame,
    ) {
    }
}
