<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Geometry;

use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Circle;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Point;
use Atelier\Layout\Geometry\StrokePlacement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Circle::class)]
#[CoversClass(StrokePlacement::class)]
final class CircleTest extends TestCase
{
    public function testBoundingRectUsesRadiusAroundCenter(): void
    {
        $circle = new Circle(new Point(50, 60), 20);
        $rect = $circle->boundingRect();

        $this->assertSame(30.0, $rect->x);
        $this->assertSame(40.0, $rect->y);
        $this->assertSame(40.0, $rect->width);
        $this->assertSame(40.0, $rect->height);
    }

    public function testInnerRadiusAccountsForPaddingAndContainedStroke(): void
    {
        $circle = new Circle(new Point(100, 100), 50);

        $innerRadius = $circle->innerRadius(
            padding: Insets::all(8),
            strokeWidth: 10,
            strokePlacement: StrokePlacement::Inside,
        );

        $this->assertSame(32.0, $innerRadius);
    }

    public function testSafeSquareFitsInsideInnerCircle(): void
    {
        $circle = new Circle(new Point(100, 100), 50);
        $safe = $circle->safeSquare(Insets::all(8), strokeWidth: 10, strokePlacement: StrokePlacement::Inside);

        $this->assertEqualsWithDelta(45.25, $safe->width, 0.01);
        $this->assertEqualsWithDelta(77.37, $safe->x, 0.01);
        $this->assertEqualsWithDelta(77.37, $safe->y, 0.01);
    }

    public function testRejectsNegativeRadius(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Circle(new Point(0, 0), -1);
    }

    public function testInnerRadiusRejectsNegativeStrokeWidth(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Circle(new Point(0, 0), 5))->innerRadius(strokeWidth: -1.0);
    }
}
