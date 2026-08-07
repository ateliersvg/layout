<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Geometry;

use Atelier\Layout\Anchor;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Rect::class)]
#[CoversClass(Insets::class)]
final class RectTest extends TestCase
{
    public function testInsetReturnsInnerRect(): void
    {
        $rect = new Rect(10, 20, 100, 80);
        $inner = $rect->inset(new Insets(5, 10, 15, 20));

        $this->assertSame(30.0, $inner->x);
        $this->assertSame(25.0, $inner->y);
        $this->assertSame(70.0, $inner->width);
        $this->assertSame(60.0, $inner->height);
    }

    public function testPointAtAnchor(): void
    {
        $rect = new Rect(10, 20, 100, 80);
        $center = $rect->pointAt(Anchor::Center);
        $bottomRight = $rect->pointAt(Anchor::BottomRight);

        $this->assertSame(60.0, $center->x);
        $this->assertSame(60.0, $center->y);
        $this->assertSame(110.0, $bottomRight->x);
        $this->assertSame(100.0, $bottomRight->y);
    }

    public function testRejectsNegativeDimensions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Rect(0, 0, -1, 10);
    }

    public function testContainsRectAcceptsAnInnerRectAndRejectsAnOverhang(): void
    {
        $outer = new Rect(0, 0, 100, 100);

        self::assertTrue($outer->containsRect(new Rect(10, 10, 80, 80)));
        self::assertTrue($outer->containsRect($outer), 'a rect contains itself');
        self::assertFalse($outer->containsRect(new Rect(-1, 10, 20, 20)), 'overhang left');
        self::assertFalse($outer->containsRect(new Rect(90, 10, 20, 20)), 'overhang right');
        self::assertFalse($outer->containsRect(new Rect(10, -1, 20, 20)), 'overhang top');
        self::assertFalse($outer->containsRect(new Rect(10, 90, 20, 20)), 'overhang bottom');
    }

    public function testContainsRectToleratesSubPixelDrift(): void
    {
        $outer = new Rect(0, 0, 100, 100);

        // Percentage insets and fractional tracks routinely land a hair past an
        // edge; that must not read as overflow.
        self::assertTrue($outer->containsRect(new Rect(0, 0, 100.0000000001, 100)));
        self::assertFalse($outer->containsRect(new Rect(0, 0, 100.01, 100)));
    }
}
