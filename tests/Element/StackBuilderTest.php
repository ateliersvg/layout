<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Axis;
use Atelier\Layout\Distribution;
use Atelier\Layout\Element\ContainerBuilder;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\NodeListBuilder;
use Atelier\Layout\Element\Stack;
use Atelier\Layout\Element\StackBuilder;
use Atelier\Layout\Element\TextBlock;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StackBuilder::class)]
#[CoversClass(ContainerBuilder::class)]
#[CoversClass(NodeListBuilder::class)]
final class StackBuilderTest extends TestCase
{
    public function testBuilderProducesTheSameGeometryAsTheImmutableApi(): void
    {
        $ctx = new LayoutContext();
        $rect = Rect::fromSize(100, 100);

        $built = StackBuilder::row('s')
            ->gap(8)
            ->alignItems(Alignment::Center)
            ->distribute(Distribution::SpaceBetween)
            ->add(Frame::fixed('a', 10, 20))
            ->add(Frame::fixed('b', 10, 20))
            ->solve($ctx, $rect);

        $manual = (new Stack(
            's',
            Axis::Horizontal,
            [Frame::fixed('a', 10, 20), Frame::fixed('b', 10, 20)],
            8.0,
            Alignment::Center,
            Distribution::SpaceBetween,
        ))->solve($ctx, $rect);

        self::assertEquals($manual->frameOf('a'), $built->frameOf('a'));
        self::assertEquals($manual->frameOf('b'), $built->frameOf('b'));
    }

    public function testBuildersNestAsChildren(): void
    {
        $ctx = new LayoutContext();
        $rect = Rect::fromSize(100, 100);

        $solved = StackBuilder::column('outer')
            ->gap(4)
            ->add(StackBuilder::row('inner')->add(Frame::fixed('x', 10, 10)))
            ->solve($ctx, $rect);

        self::assertInstanceOf(Rect::class, $solved->frameOf('x'));
    }

    public function testAlignToBaselineIsFluentAndReachesTheBuiltStack(): void
    {
        $builder = StackBuilder::row('row')
            ->add(TextBlock::of('big', 'Ag', 24))
            ->add(TextBlock::of('small', 'Ag', 10));

        self::assertSame($builder, $builder->alignToBaseline());

        $aligned = $builder->solve(new LayoutContext(), Rect::fromSize(300, 60));
        self::assertGreaterThan(0.0, $aligned->frameOf('small')?->y);

        self::assertSame($builder, $builder->alignToBaseline(false));

        $reset = $builder->solve(new LayoutContext(), Rect::fromSize(300, 60));
        self::assertSame(0.0, $reset->frameOf('small')?->y);
    }
}
