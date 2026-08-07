<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Aspect;

use Atelier\Layout\Anchor;
use Atelier\Layout\Aspect\AspectFrame;
use Atelier\Layout\Aspect\PlacedAspectFrame;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Fit\FitMode;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Value\InsetSpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AspectFrame::class)]
#[CoversClass(PlacedAspectFrame::class)]
#[CoversClass(Rect::class)]
#[CoversClass(FitMode::class)]
#[CoversClass(Anchor::class)]
#[CoversClass(InvalidArgumentException::class)]
final class AspectFrameTest extends TestCase
{
    public function testContainModeCentersAnAspectFrameInsidePadding(): void
    {
        $layout = AspectFrame::of('fitted frame', 16, 9)
            ->padding(InsetSpec::px(4))
            ->place(Rect::fromSize(200, 120));

        $this->assertSame(200.0, $layout->frame->width);
        $this->assertSame(120.0, $layout->frame->height);
        $this->assertSame(4.0, $layout->contentFrame->x);
        $this->assertSame(4.0, $layout->contentFrame->y);
        $this->assertSame(192.0, $layout->contentFrame->width);
        $this->assertSame(112.0, $layout->contentFrame->height);
        $this->assertSame(192.0, $layout->fittedFrame->width);
        $this->assertSame(108.0, $layout->fittedFrame->height);
        $this->assertSame(4.0, $layout->fittedFrame->x);
        $this->assertSame(6.0, $layout->fittedFrame->y);
        $this->assertFalse($layout->overflowX);
        $this->assertFalse($layout->overflowY);
    }

    public function testCoverModeCanOverflowTheContentFrame(): void
    {
        $layout = AspectFrame::of('fitted frame', 16, 9)
            ->fitMode(FitMode::Cover)
            ->anchor(Anchor::Center)
            ->place(new Rect(10, 20, 80, 80));

        $this->assertTrue($layout->overflowX);
        $this->assertFalse($layout->overflowY);
        $this->assertSame(-21.111111111111114, $layout->fittedFrame->x);
        $this->assertSame(20.0, $layout->fittedFrame->y);
        $this->assertEqualsWithDelta(142.22222222222223, $layout->fittedFrame->width, 0.0000000000001);
        $this->assertSame(80.0, $layout->fittedFrame->height);
    }

    public function testRejectsNonPositiveRatios(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AspectFrame::of('fitted frame', 0, 9);
    }
}
