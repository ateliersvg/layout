<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Element;

use Atelier\Layout\Anchor;
use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Overlay;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Utility\Snap;
use Atelier\Layout\Value\InsetSpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Overlay::class)]
#[CoversClass(Frame::class)]
#[CoversClass(Snap::class)]
final class OverlayTest extends TestCase
{
    public function testOverlayCentersChildrenByDefault(): void
    {
        $overlay = Overlay::of('overlay')
            ->add(Frame::fixed('badge', 20, 10));

        $result = $overlay->solve(new LayoutContext(), Rect::fromSize(100, 50));

        $this->assertSame(40.0, $result->frameOf('badge')?->x);
        $this->assertSame(20.0, $result->frameOf('badge')?->y);
    }

    public function testOverlayCanSnapChildToTargetAnchorWithOffset(): void
    {
        $overlay = Overlay::of('overlay')
            ->add(Frame::fixed('label', 30, 10), Anchor::BottomCenter, Anchor::BottomCenter, offsetY: -8);

        $result = $overlay->solve(new LayoutContext(), Rect::fromSize(100, 50));

        $this->assertSame(35.0, $result->frameOf('label')?->x);
        $this->assertSame(32.0, $result->frameOf('label')?->y);
    }

    public function testOverlayResolvesPercentPaddingBeforeSnapping(): void
    {
        $overlay = Overlay::of('overlay')
            ->padding(InsetSpec::percent(10))
            ->add(Frame::fixed('label', 10, 10), Anchor::TopLeft, Anchor::TopLeft);

        $result = $overlay->solve(new LayoutContext(), Rect::fromSize(100, 50));

        $this->assertSame(10.0, $result->frameOf('label')?->x);
        $this->assertSame(5.0, $result->frameOf('label')?->y);
    }

    public function testOverlayMeasureTakesChildMaximaPlusPadding(): void
    {
        $overlay = Overlay::of('overlay')
            ->padding(InsetSpec::px(5))
            ->add(Frame::fixed('a', 30, 20))
            ->add(Frame::fixed('b', 50, 10));

        $measure = $overlay->measure(new LayoutContext(), new BoxConstraints(maxWidth: 100, maxHeight: 100));

        $this->assertSame(60.0, $measure->size->width);
        $this->assertSame(30.0, $measure->size->height);
    }

    public function testOverlayMeasureWithoutConstraintsOrPadding(): void
    {
        $overlay = Overlay::of('overlay')
            ->add(Frame::fixed('a', 30, 20));

        $measure = $overlay->measure(new LayoutContext(), BoxConstraints::unconstrained());

        $this->assertSame(30.0, $measure->size->width);
        $this->assertSame(20.0, $measure->size->height);
    }

    public function testOverlayExposesItsId(): void
    {
        $this->assertSame('overlay', Overlay::of('overlay')->id());
    }

    public function testTheBuiltOverlayCarriesTheIdThroughBuild(): void
    {
        // The entry point returns a builder, so the node's own id()
        // is only reachable past build().
        self::assertSame('toolbar', Overlay::of('toolbar')->build()->id());
    }
}
