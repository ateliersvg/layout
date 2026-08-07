<?php

declare(strict_types=1);

namespace Atelier\Layout\Connection;

use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Point;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\RectIndex;
use Atelier\Layout\Geometry\Size;

final readonly class ConnectionEndpointBadge
{
    /**
     * @param list<string> $avoidIgnore
     */
    public function __construct(
        private OrthogonalConnection $connection,
        private Size $size,
        private Insets $padding,
        private ConnectionEndpointBadgePlacement $placement,
        private ?RectIndex $avoidIndex = null,
        private array $avoidIgnore = [],
    ) {
    }

    public static function for(OrthogonalConnection $connection, ConnectionEndpointBadgePlacement $placement): self
    {
        return new self($connection, new Size(0.0, 0.0), Insets::zero(), $placement);
    }

    public function size(Size $size): self
    {
        return new self($this->connection, $size, $this->padding, $this->placement, $this->avoidIndex, $this->avoidIgnore);
    }

    public function padding(Insets $padding): self
    {
        return new self($this->connection, $this->size, $padding, $this->placement, $this->avoidIndex, $this->avoidIgnore);
    }

    /**
     * @param list<string> $ignore
     */
    public function avoid(RectIndex $index, array $ignore = []): self
    {
        return new self($this->connection, $this->size, $this->padding, $this->placement, $index, $ignore);
    }

    public function place(): PlacedConnectionEndpointBadge
    {
        $segmentIndex = ConnectionEndpointBadgePlacement::Start === $this->placement ? 0 : \count($this->connection->segments) - 1;
        $segment = $this->connection->segmentAt($segmentIndex);
        foreach ($this->anchorsFor($segment) as $anchor) {
            $frame = $this->frameFor($anchor);
            if (null !== $this->avoidIndex && !$this->avoidIndex->isFree($frame, $this->avoidIgnore)) {
                continue;
            }

            return new PlacedConnectionEndpointBadge($frame, $anchor, $segmentIndex, $this->placement);
        }

        $anchor = ConnectionEndpointBadgePlacement::Start === $this->placement
            ? $this->startAnchor($segment)
            : $this->endAnchor($segment);

        return new PlacedConnectionEndpointBadge($this->frameFor($anchor), $anchor, $segmentIndex, $this->placement);
    }

    /**
     * @return list<Point>
     */
    private function anchorsFor(ConnectionSegment $segment): array
    {
        $anchors = [
            ConnectionEndpointBadgePlacement::Start === $this->placement ? $this->startAnchor($segment) : $this->endAnchor($segment),
        ];
        $step = max($this->size->width, $this->size->height) + max($this->padding->horizontal(), $this->padding->vertical());
        if ($step <= 0.0) {
            return $anchors;
        }

        $anchors[] = ConnectionEndpointBadgePlacement::Start === $this->placement
            ? $this->startAnchor($segment, $step)
            : $this->endAnchor($segment, $step);
        $anchors[] = ConnectionEndpointBadgePlacement::Start === $this->placement
            ? $this->startAnchor($segment, $step * 2.0)
            : $this->endAnchor($segment, $step * 2.0);

        return $anchors;
    }

    private function startAnchor(ConnectionSegment $segment, float $extra = 0.0): Point
    {
        $midX = ($segment->start->x + $segment->end->x) / 2.0;
        $midY = ($segment->start->y + $segment->end->y) / 2.0;

        if ($segment->isHorizontal()) {
            $direction = $segment->end->x >= $segment->start->x ? -1.0 : 1.0;
            $padding = $direction < 0.0 ? $this->padding->left : $this->padding->right;

            return new Point($segment->start->x + $direction * ($padding + $extra + $this->size->width / 2.0), $midY);
        }

        $direction = $segment->end->y >= $segment->start->y ? -1.0 : 1.0;
        $padding = $direction < 0.0 ? $this->padding->top : $this->padding->bottom;

        return new Point($midX, $segment->start->y + $direction * ($padding + $extra + $this->size->height / 2.0));
    }

    private function endAnchor(ConnectionSegment $segment, float $extra = 0.0): Point
    {
        $midX = ($segment->start->x + $segment->end->x) / 2.0;
        $midY = ($segment->start->y + $segment->end->y) / 2.0;

        if ($segment->isHorizontal()) {
            $direction = $segment->end->x >= $segment->start->x ? 1.0 : -1.0;
            $padding = $direction > 0.0 ? $this->padding->right : $this->padding->left;

            return new Point($segment->end->x + $direction * ($padding + $extra + $this->size->width / 2.0), $midY);
        }

        $direction = $segment->end->y >= $segment->start->y ? 1.0 : -1.0;
        $padding = $direction > 0.0 ? $this->padding->bottom : $this->padding->top;

        return new Point($midX, $segment->end->y + $direction * ($padding + $extra + $this->size->height / 2.0));
    }

    private function frameFor(Point $anchor): Rect
    {
        return new Rect(
            $anchor->x - $this->size->width / 2.0,
            $anchor->y - $this->size->height / 2.0,
            $this->size->width,
            $this->size->height,
        );
    }
}
