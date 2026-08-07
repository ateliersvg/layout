<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Group;
use Atelier\Layout\Element\GroupBuilder;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GroupBuilder::class)]
final class GroupBuilderTest extends TestCase
{
    public function testBuilderProducesTheSameGeometryAsTheImmutableApi(): void
    {
        $ctx = new LayoutContext();
        $rect = Rect::fromSize(100, 100);

        $built = GroupBuilder::of('c')
            ->align(Alignment::End, Alignment::Start)
            ->add(Frame::fixed('a', 10, 10))
            ->add(Frame::fixed('b', 10, 10))
            ->solve($ctx, $rect);

        $manual = (new Group(
            'c',
            [Frame::fixed('a', 10, 10), Frame::fixed('b', 10, 10)],
            Alignment::End,
            Alignment::Start,
        ))->solve($ctx, $rect);

        self::assertEquals($manual->frameOf('a'), $built->frameOf('a'));
        self::assertEquals($manual->frameOf('b'), $built->frameOf('b'));
    }
}
