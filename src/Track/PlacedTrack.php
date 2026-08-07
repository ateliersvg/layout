<?php

declare(strict_types=1);

namespace Atelier\Layout\Track;

use Atelier\Layout\Geometry\Rect;

final readonly class PlacedTrack
{
    public function __construct(
        public string $id,
        public Rect $frame,
        public Rect $headerFrame,
        public Rect $bodyFrame,
        public Rect $footerFrame,
    ) {
    }
}
