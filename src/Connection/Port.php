<?php

declare(strict_types=1);

namespace Atelier\Layout\Connection;

use Atelier\Layout\Anchor;
use Atelier\Layout\Geometry\Point;
use Atelier\Layout\Geometry\Rect;

final readonly class Port
{
    public function __construct(
        public Point $point,
        public PortSide $side,
    ) {
    }

    public static function on(Rect $rect, PortSide $side): self
    {
        $anchor = match ($side) {
            PortSide::Top => Anchor::TopCenter,
            PortSide::Right => Anchor::CenterRight,
            PortSide::Bottom => Anchor::BottomCenter,
            PortSide::Left => Anchor::CenterLeft,
        };

        return new self($rect->pointAt($anchor), $side);
    }
}
