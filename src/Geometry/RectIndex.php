<?php

declare(strict_types=1);

namespace Atelier\Layout\Geometry;

/**
 * Immutable rectangle index for small collision and occupancy queries.
 *
 * Edge-touching is not considered an intersection. Queries are stable in the
 * order rectangles were added.
 */
final readonly class RectIndex
{
    /**
     * @param array<string, Rect> $rects
     */
    private function __construct(
        private array $rects,
    ) {
    }

    /**
     * @param array<string, Rect> $rects
     */
    public static function from(array $rects): self
    {
        return new self($rects);
    }

    /**
     * @return list<string>
     */
    public function intersecting(Rect $query): array
    {
        $hits = [];
        foreach ($this->rects as $id => $rect) {
            if (self::intersects($rect, $query)) {
                $hits[] = $id;
            }
        }

        return $hits;
    }

    /**
     * @param list<string> $ignore
     */
    public function isFree(Rect $query, array $ignore = []): bool
    {
        $ignore = array_fill_keys($ignore, true);

        foreach ($this->rects as $id => $rect) {
            if (isset($ignore[$id])) {
                continue;
            }

            if (self::intersects($rect, $query)) {
                return false;
            }
        }

        return true;
    }

    private static function intersects(Rect $a, Rect $b): bool
    {
        return $a->x < $b->right()
            && $a->right() > $b->x
            && $a->y < $b->bottom()
            && $a->bottom() > $b->y;
    }
}
