<?php

declare(strict_types=1);

namespace Atelier\Layout\Fit;

use Atelier\Layout\Anchor;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;

final readonly class Fit
{
    public static function rect(Size $source, Rect $target, FitMode $mode = FitMode::Contain, Anchor $anchor = Anchor::Center): Rect
    {
        // Zero in, zero out. A collapsed panel, an empty list or a track that
        // received no space are ordinary states, not caller mistakes, so they
        // travel through the stack without an exception. Returning early also
        // keeps the ratios below from dividing by zero and seeding a NaN that
        // would spread silently into output coordinates.
        if (0.0 === $source->width || 0.0 === $source->height) {
            return self::anchored(new Size(0.0, 0.0), $target, $anchor);
        }

        [$width, $height] = match ($mode) {
            FitMode::Fill => [$target->width, $target->height],
            FitMode::None => [$source->width, $source->height],
            FitMode::Contain => self::scaled($source, $target, min($target->width / $source->width, $target->height / $source->height)),
            FitMode::Cover => self::scaled($source, $target, max($target->width / $source->width, $target->height / $source->height)),
            FitMode::ScaleDown => self::scaleDown($source, $target),
        };

        return self::anchored(new Size($width, $height), $target, $anchor);
    }

    /**
     * @return array{float, float}
     */
    private static function scaled(Size $source, Rect $target, float $scale): array
    {
        return [$source->width * $scale, $source->height * $scale];
    }

    /**
     * @return array{float, float}
     */
    private static function scaleDown(Size $source, Rect $target): array
    {
        if ($source->width <= $target->width && $source->height <= $target->height) {
            return [$source->width, $source->height];
        }

        return self::scaled($source, $target, min($target->width / $source->width, $target->height / $source->height));
    }

    private static function anchored(Size $size, Rect $target, Anchor $anchor): Rect
    {
        $x = match ($anchor) {
            Anchor::TopLeft, Anchor::CenterLeft, Anchor::BottomLeft => $target->x,
            Anchor::TopCenter, Anchor::Center, Anchor::BottomCenter => $target->x + ($target->width - $size->width) / 2.0,
            Anchor::TopRight, Anchor::CenterRight, Anchor::BottomRight => $target->right() - $size->width,
        };
        $y = match ($anchor) {
            Anchor::TopLeft, Anchor::TopCenter, Anchor::TopRight => $target->y,
            Anchor::CenterLeft, Anchor::Center, Anchor::CenterRight => $target->y + ($target->height - $size->height) / 2.0,
            Anchor::BottomLeft, Anchor::BottomCenter, Anchor::BottomRight => $target->bottom() - $size->height,
        };

        return new Rect($x, $y, $size->width, $size->height);
    }
}
