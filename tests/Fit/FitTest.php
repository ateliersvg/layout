<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Fit;

use Atelier\Layout\Anchor;
use Atelier\Layout\Fit\Fit;
use Atelier\Layout\Fit\FitMode;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Fit::class)]
#[CoversClass(FitMode::class)]
final class FitTest extends TestCase
{
    public function testContainPreservesAspectInsideTarget(): void
    {
        $rect = Fit::rect(new Size(100, 50), Rect::fromSize(80, 80), FitMode::Contain);

        $this->assertSame(0.0, $rect->x);
        $this->assertSame(20.0, $rect->y);
        $this->assertSame(80.0, $rect->width);
        $this->assertSame(40.0, $rect->height);
    }

    public function testCoverPreservesAspectAndCoversTarget(): void
    {
        $rect = Fit::rect(new Size(100, 50), Rect::fromSize(80, 80), FitMode::Cover);

        $this->assertSame(-40.0, $rect->x);
        $this->assertSame(0.0, $rect->y);
        $this->assertSame(160.0, $rect->width);
        $this->assertSame(80.0, $rect->height);
    }

    public function testFillUsesTargetSize(): void
    {
        $rect = Fit::rect(new Size(100, 50), new Rect(10, 20, 80, 60), FitMode::Fill);

        $this->assertSame(10.0, $rect->x);
        $this->assertSame(20.0, $rect->y);
        $this->assertSame(80.0, $rect->width);
        $this->assertSame(60.0, $rect->height);
    }

    public function testNoneUsesSourceSizeAndAnchor(): void
    {
        $rect = Fit::rect(new Size(20, 10), new Rect(10, 20, 80, 60), FitMode::None, Anchor::BottomRight);

        $this->assertSame(70.0, $rect->x);
        $this->assertSame(70.0, $rect->y);
        $this->assertSame(20.0, $rect->width);
        $this->assertSame(10.0, $rect->height);
    }

    public function testScaleDownKeepsSourceWhenItAlreadyFits(): void
    {
        $rect = Fit::rect(new Size(20, 10), new Rect(10, 20, 80, 60), FitMode::ScaleDown, Anchor::TopLeft);

        $this->assertSame(10.0, $rect->x);
        $this->assertSame(20.0, $rect->y);
        $this->assertSame(20.0, $rect->width);
        $this->assertSame(10.0, $rect->height);
    }

    public function testScaleDownShrinksSourceThatExceedsTarget(): void
    {
        $rect = Fit::rect(new Size(200, 100), Rect::fromSize(80, 80), FitMode::ScaleDown);

        $this->assertSame(0.0, $rect->x);
        $this->assertSame(20.0, $rect->y);
        $this->assertSame(80.0, $rect->width);
        $this->assertSame(40.0, $rect->height);
    }

    public function testReturnsEmptyGeometryForAZeroSizeSource(): void
    {
        // This used to throw. Every other primitive answers a degenerate input
        // with empty geometry, and a collapsed source is a state, not a mistake.
        $fitted = Fit::rect(new Size(0, 10), Rect::fromSize(100, 100));

        self::assertSame(0.0, $fitted->width);
        self::assertSame(0.0, $fitted->height);
    }
}
