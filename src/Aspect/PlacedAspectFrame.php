<?php

declare(strict_types=1);

namespace Atelier\Layout\Aspect;

use Atelier\Layout\Geometry\Rect;

final readonly class PlacedAspectFrame
{
    public function __construct(
        public string $id,
        public Rect $frame,
        public Rect $contentFrame,
        public Rect $fittedFrame,
        public bool $overflowX,
        public bool $overflowY,
    ) {
    }
}
