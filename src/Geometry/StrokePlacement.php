<?php

declare(strict_types=1);

namespace Atelier\Layout\Geometry;

enum StrokePlacement
{
    case Inside;
    case Center;
    case Outside;

    public function innerInset(float $strokeWidth): float
    {
        return match ($this) {
            self::Inside => $strokeWidth,
            self::Center => $strokeWidth / 2.0,
            self::Outside => 0.0,
        };
    }
}
