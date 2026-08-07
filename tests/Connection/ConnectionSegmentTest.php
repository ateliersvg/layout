<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Connection;

use Atelier\Layout\Axis;
use Atelier\Layout\Connection\ConnectionSegment;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Point;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConnectionSegment::class)]
#[CoversClass(Axis::class)]
#[CoversClass(Point::class)]
#[CoversClass(InvalidArgumentException::class)]
final class ConnectionSegmentTest extends TestCase
{
    public function testExposesOrientation(): void
    {
        $segment = new ConnectionSegment(2, new Point(10, 20), new Point(40, 20), Axis::Horizontal);

        $this->assertSame(2, $segment->index);
        $this->assertTrue($segment->isHorizontal());
        $this->assertFalse($segment->isVertical());
    }

    public function testRejectsNegativeIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ConnectionSegment(-1, new Point(0, 0), new Point(1, 0), Axis::Horizontal);
    }
}
