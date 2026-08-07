<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Connection;

use Atelier\Layout\Axis;
use Atelier\Layout\Connection\ConnectionSegment;
use Atelier\Layout\Connection\OrthogonalConnection;
use Atelier\Layout\Connection\Port;
use Atelier\Layout\Connection\PortSide;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Point;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OrthogonalConnection::class)]
#[CoversClass(ConnectionSegment::class)]
#[CoversClass(Port::class)]
#[CoversClass(PortSide::class)]
#[CoversClass(InvalidArgumentException::class)]
final class OrthogonalConnectionTest extends TestCase
{
    /**
     * @return non-empty-list<Point>
     */
    private function elbowPoints(): array
    {
        return [
            new Point(0, 0),
            new Point(50, 0),
            new Point(50, 40),
        ];
    }

    private function elbowConnection(): OrthogonalConnection
    {
        $points = $this->elbowPoints();

        return new OrthogonalConnection(
            new Port(new Point(0, 0), PortSide::Right),
            new Port(new Point(50, 40), PortSide::Top),
            $points,
            OrthogonalConnection::segmentsForPoints($points),
            new Point(50, 20),
            new Point(0, 25),
        );
    }

    public function testStartAndEndPointReturnListBoundaries(): void
    {
        $connection = $this->elbowConnection();

        $this->assertSame(0.0, $connection->startPoint()->x);
        $this->assertSame(0.0, $connection->startPoint()->y);
        $this->assertSame(50.0, $connection->endPoint()->x);
        $this->assertSame(40.0, $connection->endPoint()->y);
    }

    public function testIsStraightIsFalseForMultiSegmentConnection(): void
    {
        $this->assertFalse($this->elbowConnection()->isStraight());
    }

    public function testIsStraightIsTrueForSingleSegmentConnection(): void
    {
        $points = [new Point(0, 0), new Point(80, 0)];

        $connection = new OrthogonalConnection(
            new Port(new Point(0, 0), PortSide::Right),
            new Port(new Point(80, 0), PortSide::Left),
            $points,
            OrthogonalConnection::segmentsForPoints($points),
            new Point(40, 0),
            new Point(20, 0),
        );

        $this->assertTrue($connection->isStraight());
    }

    public function testFirstAndLastSegmentExposeBoundaries(): void
    {
        $connection = $this->elbowConnection();

        $this->assertSame(0, $connection->firstSegment()->index);
        $this->assertSame(1, $connection->lastSegment()->index);
        $this->assertSame($connection->segments[1], $connection->segmentAt(1));
    }

    public function testSegmentAtThrowsForOutOfRangeIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->elbowConnection()->segmentAt(5);
    }

    public function testSegmentsForPointsClassifiesAxesByDelta(): void
    {
        $segments = OrthogonalConnection::segmentsForPoints($this->elbowPoints());

        $this->assertCount(2, $segments);

        $this->assertSame(0, $segments[0]->index);
        $this->assertSame(Axis::Horizontal, $segments[0]->axis);
        $this->assertTrue($segments[0]->isHorizontal());
        $this->assertSame(0.0, $segments[0]->start->x);
        $this->assertSame(50.0, $segments[0]->end->x);

        $this->assertSame(1, $segments[1]->index);
        $this->assertSame(Axis::Vertical, $segments[1]->axis);
        $this->assertTrue($segments[1]->isVertical());
        $this->assertSame(40.0, $segments[1]->end->y);
    }

    public function testSegmentsForPointsReturnsEmptyForSinglePoint(): void
    {
        $this->assertSame([], OrthogonalConnection::segmentsForPoints([new Point(0, 0)]));
    }

    public function testConstructorRejectsFewerThanTwoPoints(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OrthogonalConnection(
            new Port(new Point(0, 0), PortSide::Right),
            new Port(new Point(0, 0), PortSide::Left),
            [new Point(0, 0)],
            [],
            new Point(0, 0),
            new Point(1, 0),
        );
    }

    public function testConstructorRejectsSegmentCountMismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OrthogonalConnection(
            new Port(new Point(0, 0), PortSide::Right),
            new Port(new Point(1, 0), PortSide::Left),
            [new Point(0, 0), new Point(1, 0)],
            [],
            new Point(0, 0),
            new Point(1, 0),
        );
    }
}
