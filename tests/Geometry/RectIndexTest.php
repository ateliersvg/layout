<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Geometry;

use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\RectIndex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RectIndex::class)]
#[CoversClass(Rect::class)]
final class RectIndexTest extends TestCase
{
    public function testReturnsStableHitsInInsertionOrder(): void
    {
        $index = RectIndex::from([
            'node.a' => new Rect(10, 10, 20, 20),
            'node.b' => new Rect(25, 15, 20, 20),
            'node.c' => new Rect(60, 60, 10, 10),
        ]);

        $hits = $index->intersecting(new Rect(18, 18, 24, 12));

        $this->assertSame(['node.a', 'node.b'], $hits);
    }

    public function testTreatsEdgeTouchingAsFreeSpace(): void
    {
        $index = RectIndex::from([
            'node.a' => new Rect(10, 10, 20, 20),
        ]);

        $this->assertTrue($index->isFree(new Rect(30, 10, 8, 8)));
        $this->assertSame([], $index->intersecting(new Rect(30, 10, 8, 8)));
    }

    public function testIgnoreListSkipsMatchingIds(): void
    {
        $index = RectIndex::from([
            'edge.ab.label' => new Rect(20, 20, 15, 8),
            'node.a' => new Rect(80, 80, 20, 20),
        ]);

        $labelFrame = new Rect(18, 18, 10, 10);

        $this->assertFalse($index->isFree($labelFrame));
        $this->assertTrue($index->isFree($labelFrame, ignore: ['edge.ab.label']));
        $this->assertSame(['edge.ab.label'], $index->intersecting($labelFrame));
    }
}
