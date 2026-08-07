<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Geometry;

use Atelier\Layout\Geometry\Bounds;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Bounds::class)]
#[CoversClass(Insets::class)]
#[CoversClass(Rect::class)]
final class BoundsTest extends TestCase
{
    public function testComputesUnionOfRects(): void
    {
        $bounds = Bounds::of(
            new Rect(10, 30, 20, 10),
            new Rect(25, 15, 10, 50),
            new Rect(0, 40, 4, 4),
        );

        $this->assertSame(0.0, $bounds->x);
        $this->assertSame(15.0, $bounds->y);
        $this->assertSame(35.0, $bounds->width);
        $this->assertSame(50.0, $bounds->height);
    }

    public function testEmptyBoundsAreNull(): void
    {
        $this->assertNull(Bounds::fromRects([]));
    }

    public function testFromRectsComputesUnionOfNonEmptyInput(): void
    {
        $bounds = Bounds::fromRects([
            new Rect(10, 30, 20, 10),
            new Rect(25, 15, 10, 50),
            new Rect(0, 40, 4, 4),
        ]);

        $this->assertNotNull($bounds);
        $this->assertSame(0.0, $bounds->x);
        $this->assertSame(15.0, $bounds->y);
        $this->assertSame(35.0, $bounds->width);
        $this->assertSame(50.0, $bounds->height);
    }

    public function testExpandsRectWithInsets(): void
    {
        $expanded = Bounds::expand(new Rect(10, 20, 30, 40), new Insets(1, 2, 3, 4));

        $this->assertSame(6.0, $expanded->x);
        $this->assertSame(19.0, $expanded->y);
        $this->assertSame(36.0, $expanded->width);
        $this->assertSame(44.0, $expanded->height);
    }
}
