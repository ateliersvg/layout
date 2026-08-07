<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Connection;

use Atelier\Layout\Connection\ConnectionSegment;
use Atelier\Layout\Connection\OrthogonalConnection;
use Atelier\Layout\Connection\OrthogonalConnector;
use Atelier\Layout\Connection\Port;
use Atelier\Layout\Connection\PortSide;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Point;
use Atelier\Layout\Geometry\Rect;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OrthogonalConnector::class)]
#[CoversClass(OrthogonalConnection::class)]
#[CoversClass(Port::class)]
#[CoversClass(PortSide::class)]
#[CoversClass(ConnectionSegment::class)]
#[CoversClass(InvalidArgumentException::class)]
final class OrthogonalConnectorTest extends TestCase
{
    public function testConnectsHorizontallyBetweenSeparatedRects(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 35, 50, 30),
        );

        $this->assertFalse($connection->isStraight());
        $this->assertSame(50.0, $connection->startPoint()->x);
        $this->assertSame(35.0, $connection->startPoint()->y);
        $this->assertSame(100.0, $connection->endPoint()->x);
        $this->assertSame(50.0, $connection->endPoint()->y);
        $this->assertSame(75.0, $connection->labelPoint->x);
        $this->assertSame(42.5, $connection->labelPoint->y);
        $this->assertSame(25.0, $connection->tipTangent->x);
        $this->assertSame(0.0, $connection->tipTangent->y);
        $this->assertCount(3, $connection->segments);
        $this->assertTrue($connection->segments[0]->isHorizontal());
        $this->assertTrue($connection->segments[1]->isVertical());
        $this->assertTrue($connection->segments[2]->isHorizontal());
    }

    public function testConnectsVerticallyBetweenSeparatedRects(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(15, 100, 50, 30),
        );

        $this->assertFalse($connection->isStraight());
        $this->assertSame(30.0, $connection->startPoint()->x);
        $this->assertSame(50.0, $connection->startPoint()->y);
        $this->assertSame(40.0, $connection->endPoint()->x);
        $this->assertSame(100.0, $connection->endPoint()->y);
        $this->assertSame(35.0, $connection->labelPoint->x);
        $this->assertSame(75.0, $connection->labelPoint->y);
        $this->assertSame(0.0, $connection->tipTangent->x);
        $this->assertSame(25.0, $connection->tipTangent->y);
        $this->assertCount(3, $connection->segments);
        $this->assertTrue($connection->segments[0]->isVertical());
        $this->assertTrue($connection->segments[1]->isHorizontal());
        $this->assertTrue($connection->segments[2]->isVertical());
    }

    public function testConnectsStraightWhenPortsShareAnAxis(): void
    {
        $connection = (new OrthogonalConnector())->connectPorts(
            new Port(new Point(10, 20), PortSide::Right),
            new Port(new Point(80, 20), PortSide::Left),
        );

        $this->assertTrue($connection->isStraight());
        $this->assertCount(2, $connection->points);
        $this->assertSame(45.0, $connection->labelPoint->x);
        $this->assertSame(20.0, $connection->labelPoint->y);
    }

    public function testRejectsInvalidConnectionPointList(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OrthogonalConnection(
            new Port(new Point(0, 0), PortSide::Right),
            new Port(new Point(1, 0), PortSide::Left),
            [new Point(0, 0)],
            [],
            new Point(0, 0),
            new Point(1, 0),
        );
    }

    public function testAccessorsExposeSegmentBoundaries(): void
    {
        $connection = (new OrthogonalConnector())->connect(
            new Rect(10, 20, 40, 30),
            new Rect(100, 35, 50, 30),
        );

        $this->assertSame(0, $connection->firstSegment()->index);
        $this->assertSame(2, $connection->lastSegment()->index);
        $this->assertSame($connection->segments[1], $connection->segmentAt(1));
    }
}
