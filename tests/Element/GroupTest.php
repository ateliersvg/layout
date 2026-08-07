<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Group;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Value\InsetSpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Group::class)]
#[CoversClass(Frame::class)]
final class GroupTest extends TestCase
{
    public function testGroupCentersChildrenByDefault(): void
    {
        $groupBounds = Group::of('group')
            ->add(Frame::fixed('box', 40, 20));

        $result = $groupBounds->solve(new LayoutContext(), Rect::fromSize(100, 80));

        $this->assertSame(30.0, $result->frameOf('box')?->x);
        $this->assertSame(30.0, $result->frameOf('box')?->y);
    }

    public function testGroupCanStretchChildren(): void
    {
        $groupBounds = Group::of('group')
            ->align(Alignment::Stretch, Alignment::Stretch)
            ->add(Frame::fixed('box', 40, 20));

        $result = $groupBounds->solve(new LayoutContext(), Rect::fromSize(100, 80));

        $this->assertSame(100.0, $result->frameOf('box')?->width);
        $this->assertSame(80.0, $result->frameOf('box')?->height);
    }

    public function testGroupMeasureTakesChildMaximaPlusPadding(): void
    {
        $groupBounds = Group::of('group')
            ->padding(InsetSpec::px(10))
            ->add(Frame::fixed('a', 40, 20))
            ->add(Frame::fixed('b', 30, 50));

        $measure = $groupBounds->measure(new LayoutContext(), new BoxConstraints(maxWidth: 200, maxHeight: 200));

        $this->assertSame(60.0, $measure->size->width);
        $this->assertSame(70.0, $measure->size->height);
    }

    public function testGroupMeasureWithoutConstraintsOrPadding(): void
    {
        $groupBounds = Group::of('group')
            ->add(Frame::fixed('a', 40, 20));

        $measure = $groupBounds->measure(new LayoutContext(), BoxConstraints::unconstrained());

        $this->assertSame(40.0, $measure->size->width);
        $this->assertSame(20.0, $measure->size->height);
    }

    public function testGroupExposesItsId(): void
    {
        $this->assertSame('group', Group::of('group')->id());
    }

    public function testTheBuiltGroupCarriesTheIdThroughBuild(): void
    {
        // The entry point returns a builder, so the node's own id()
        // is only reachable past build().
        self::assertSame('toolbar', Group::of('toolbar')->build()->id());
    }
}
