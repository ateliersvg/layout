<?php

declare(strict_types=1);

namespace Atelier\Layout\Connection;

use Atelier\Layout\Axis;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Point;

final readonly class OrthogonalConnection
{
    /**
     * @param non-empty-list<Point>   $points
     * @param list<ConnectionSegment> $segments
     */
    public function __construct(
        public Port $start,
        public Port $end,
        public array $points,
        public array $segments,
        public Point $labelPoint,
        public Point $tipTangent,
    ) {
        if (\count($points) < 2) {
            throw new InvalidArgumentException('An orthogonal connection needs at least a start and an end point.');
        }

        if (\count($segments) !== \count($points) - 1) {
            throw new InvalidArgumentException('An orthogonal connection needs one segment per point interval.');
        }
    }

    public function startPoint(): Point
    {
        return $this->points[0];
    }

    public function endPoint(): Point
    {
        return $this->points[\count($this->points) - 1];
    }

    public function isStraight(): bool
    {
        return 1 === \count($this->segments);
    }

    public function firstSegment(): ConnectionSegment
    {
        return $this->segments[0];
    }

    public function lastSegment(): ConnectionSegment
    {
        return $this->segments[\count($this->segments) - 1];
    }

    public function segmentAt(int $index): ConnectionSegment
    {
        if (!isset($this->segments[$index])) {
            throw new InvalidArgumentException(\sprintf('Orthogonal connection segment %d does not exist.', $index));
        }

        return $this->segments[$index];
    }

    /**
     * @param list<Point> $points
     *
     * @return list<ConnectionSegment>
     */
    public static function segmentsForPoints(array $points): array
    {
        $segments = [];
        for ($index = 0; $index < \count($points) - 1; ++$index) {
            $start = $points[$index];
            $end = $points[$index + 1];
            $segments[] = new ConnectionSegment(
                $index,
                $start,
                $end,
                abs($start->y - $end->y) < 0.01 ? Axis::Horizontal : Axis::Vertical,
            );
        }

        return $segments;
    }
}
