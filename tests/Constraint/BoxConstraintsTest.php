<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Constraint;

use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Size;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoxConstraints::class)]
final class BoxConstraintsTest extends TestCase
{
    public function testConstrainClampsSize(): void
    {
        $constraints = new BoxConstraints(minWidth: 10, minHeight: 20, maxWidth: 50, maxHeight: 60);

        $small = $constraints->constrain(new Size(1, 2));
        $large = $constraints->constrain(new Size(100, 200));

        $this->assertSame(10.0, $small->width);
        $this->assertSame(20.0, $small->height);
        $this->assertSame(50.0, $large->width);
        $this->assertSame(60.0, $large->height);
    }

    public function testUnconstrainedHasNoLowerOrUpperLimits(): void
    {
        $constraints = BoxConstraints::unconstrained();

        $this->assertSame(0.0, $constraints->minWidth);
        $this->assertSame(0.0, $constraints->minHeight);
        $this->assertSame(PHP_FLOAT_MAX, $constraints->maxWidth);
        $this->assertSame(PHP_FLOAT_MAX, $constraints->maxHeight);
        $this->assertNull($constraints->preferredWidth);
        $this->assertNull($constraints->preferredHeight);
    }

    public function testTightPinsEveryDimension(): void
    {
        $constraints = BoxConstraints::tight(40, 30);

        $this->assertSame(40.0, $constraints->minWidth);
        $this->assertSame(40.0, $constraints->maxWidth);
        $this->assertSame(30.0, $constraints->minHeight);
        $this->assertSame(30.0, $constraints->maxHeight);
        $this->assertSame(40.0, $constraints->preferredWidth);
        $this->assertSame(30.0, $constraints->preferredHeight);
    }

    public function testRejectsMinimumGreaterThanMaximum(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BoxConstraints(minWidth: 10, maxWidth: 5);
    }

    public function testRejectsNegativeDimensions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BoxConstraints(minWidth: -1.0);
    }
}
