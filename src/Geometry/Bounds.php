<?php

declare(strict_types=1);

namespace Atelier\Layout\Geometry;

final readonly class Bounds
{
    public static function of(Rect $rect, Rect ...$rects): Rect
    {
        $all = [$rect, ...$rects];
        $minX = $all[0]->x;
        $minY = $all[0]->y;
        $maxX = $all[0]->right();
        $maxY = $all[0]->bottom();

        for ($i = 1; $i < \count($all); ++$i) {
            $minX = min($minX, $all[$i]->x);
            $minY = min($minY, $all[$i]->y);
            $maxX = max($maxX, $all[$i]->right());
            $maxY = max($maxY, $all[$i]->bottom());
        }

        return new Rect($minX, $minY, $maxX - $minX, $maxY - $minY);
    }

    /**
     * @param iterable<Rect> $rects
     */
    public static function fromRects(iterable $rects): ?Rect
    {
        $minX = null;
        $minY = null;
        $maxX = null;
        $maxY = null;

        foreach ($rects as $rect) {
            $minX = null === $minX ? $rect->x : min($minX, $rect->x);
            $minY = null === $minY ? $rect->y : min($minY, $rect->y);
            $maxX = null === $maxX ? $rect->right() : max($maxX, $rect->right());
            $maxY = null === $maxY ? $rect->bottom() : max($maxY, $rect->bottom());
        }

        if (null === $minX || null === $minY || null === $maxX || null === $maxY) {
            return null;
        }

        return new Rect($minX, $minY, $maxX - $minX, $maxY - $minY);
    }

    public static function expand(Rect $rect, Insets $insets): Rect
    {
        return new Rect(
            $rect->x - $insets->left,
            $rect->y - $insets->top,
            $rect->width + $insets->horizontal(),
            $rect->height + $insets->vertical(),
        );
    }
}
