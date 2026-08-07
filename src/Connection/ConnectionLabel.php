<?php

declare(strict_types=1);

namespace Atelier\Layout\Connection;

use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Point;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\RectIndex;
use Atelier\Layout\Geometry\Size;

final readonly class ConnectionLabel
{
    /**
     * @param list<string> $avoidIgnore
     */
    public function __construct(
        private OrthogonalConnection $connection,
        private Size $size,
        private Insets $padding,
        private ConnectionLabelPlacement $placement,
        private ?RectIndex $avoidIndex = null,
        private array $avoidIgnore = [],
    ) {
    }

    public static function for(OrthogonalConnection $connection): self
    {
        return new self($connection, new Size(0.0, 0.0), Insets::zero(), ConnectionLabelPlacement::Centered);
    }

    public function size(Size $size): self
    {
        return new self($this->connection, $size, $this->padding, $this->placement);
    }

    public function padding(Insets $padding): self
    {
        return new self($this->connection, $this->size, $padding, $this->placement);
    }

    public function placement(ConnectionLabelPlacement $placement): self
    {
        return new self($this->connection, $this->size, $this->padding, $placement, $this->avoidIndex, $this->avoidIgnore);
    }

    /**
     * @param list<string> $ignore
     */
    public function avoid(RectIndex $index, array $ignore = []): self
    {
        return new self($this->connection, $this->size, $this->padding, $this->placement, $index, $ignore);
    }

    public function place(): PlacedConnectionLabel
    {
        foreach ($this->placementsFor($this->placement) as $placement) {
            $segmentIndex = $this->segmentIndex($placement);
            $segment = $this->connection->segmentAt($segmentIndex);
            $anchor = $this->anchorFor($segment, $placement);
            $frame = $this->frameFor($anchor);

            if (null !== $this->avoidIndex && !$this->avoidIndex->isFree($frame, $this->avoidIgnore)) {
                continue;
            }

            return new PlacedConnectionLabel($frame, $anchor, $segmentIndex, $placement);
        }

        $segmentIndex = $this->segmentIndex($this->placement);
        $segment = $this->connection->segmentAt($segmentIndex);
        $anchor = $this->anchorFor($segment, $this->placement);

        return new PlacedConnectionLabel($this->frameFor($anchor), $anchor, $segmentIndex, $this->placement);
    }

    /**
     * @return list<ConnectionLabelPlacement>
     */
    private function placementsFor(ConnectionLabelPlacement $placement): array
    {
        return match ($placement) {
            ConnectionLabelPlacement::EndpointStart, ConnectionLabelPlacement::EndpointEnd => [$placement],
            ConnectionLabelPlacement::Above => [ConnectionLabelPlacement::Above, ConnectionLabelPlacement::Centered, ConnectionLabelPlacement::Below],
            ConnectionLabelPlacement::Below => [ConnectionLabelPlacement::Below, ConnectionLabelPlacement::Centered, ConnectionLabelPlacement::Above],
            ConnectionLabelPlacement::Centered => [ConnectionLabelPlacement::Centered, ConnectionLabelPlacement::Above, ConnectionLabelPlacement::Below],
        };
    }

    private function segmentIndex(ConnectionLabelPlacement $placement): int
    {
        return match ($placement) {
            ConnectionLabelPlacement::EndpointStart => 0,
            ConnectionLabelPlacement::EndpointEnd => \count($this->connection->segments) - 1,
            default => intdiv(\count($this->connection->segments), 2),
        };
    }

    private function anchorFor(ConnectionSegment $segment, ConnectionLabelPlacement $placement): Point
    {
        $midX = ($segment->start->x + $segment->end->x) / 2.0;
        $midY = ($segment->start->y + $segment->end->y) / 2.0;

        if ($segment->isHorizontal()) {
            return match ($placement) {
                ConnectionLabelPlacement::Above => new Point($midX, $segment->start->y - $this->padding->top - $this->size->height / 2.0),
                ConnectionLabelPlacement::Below => new Point($midX, $segment->start->y + $this->padding->bottom + $this->size->height / 2.0),
                ConnectionLabelPlacement::EndpointStart => new Point($segment->start->x, $segment->start->y),
                ConnectionLabelPlacement::EndpointEnd => new Point($segment->end->x, $segment->end->y),
                default => new Point($midX, $midY),
            };
        }

        return match ($placement) {
            ConnectionLabelPlacement::Above => new Point($segment->start->x - $this->padding->left - $this->size->width / 2.0, $midY),
            ConnectionLabelPlacement::Below => new Point($segment->start->x + $this->padding->right + $this->size->width / 2.0, $midY),
            ConnectionLabelPlacement::EndpointStart => new Point($segment->start->x, $segment->start->y),
            ConnectionLabelPlacement::EndpointEnd => new Point($segment->end->x, $segment->end->y),
            default => new Point($midX, $midY),
        };
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
