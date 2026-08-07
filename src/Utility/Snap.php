<?php

declare(strict_types=1);

namespace Atelier\Layout\Utility;

use Atelier\Layout\Anchor;
use Atelier\Layout\Geometry\Rect;

final readonly class Snap
{
    public static function rect(
        Rect $subject,
        Anchor $subjectAnchor,
        Rect $target,
        Anchor $targetAnchor,
        float $offsetX = 0.0,
        float $offsetY = 0.0,
    ): Rect {
        $from = $subject->pointAt($subjectAnchor);
        $to = $target->pointAt($targetAnchor);

        return new Rect(
            $subject->x + ($to->x - $from->x) + $offsetX,
            $subject->y + ($to->y - $from->y) + $offsetY,
            $subject->width,
            $subject->height,
        );
    }
}
