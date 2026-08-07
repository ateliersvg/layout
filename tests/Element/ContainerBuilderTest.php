<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Anchor;
use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Element\ContainerBuilder;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\GridBuilder;
use Atelier\Layout\Element\GroupBuilder;
use Atelier\Layout\Element\OverlayBuilder;
use Atelier\Layout\Element\StackBuilder;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\LayoutNodeInterface;
use Atelier\Layout\Value\InsetSpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The shared builder surface is abstract, so it is exercised through the
 * concrete builders. What matters here is the promise in its docblock: a
 * builder is itself a LayoutNodeInterface, so it drops in wherever a node is
 * expected and callers never need an explicit build().
 */
#[CoversClass(ContainerBuilder::class)]
final class ContainerBuilderTest extends TestCase
{
    /**
     * @return iterable<string, array{ContainerBuilder}>
     */
    public static function builders(): iterable
    {
        // Each child is pinned to the top-left so padding is observable. A
        // centred child would sit at the same point with or without padding,
        // since padding shrinks the content rect symmetrically.
        yield 'stack' => [
            StackBuilder::column('c')
                ->alignItems(Alignment::Start)
                ->add(Frame::fixed('a', 10, 10)),
        ];
        yield 'grid' => [
            GridBuilder::columns('c', 1)
                ->align(Alignment::Start, Alignment::Start)
                ->add(Frame::fixed('a', 10, 10)),
        ];
        yield 'group' => [
            GroupBuilder::of('c')
                ->align(Alignment::Start, Alignment::Start)
                ->add(Frame::fixed('a', 10, 10)),
        ];
        yield 'overlay' => [
            OverlayBuilder::of('c')
                ->add(Frame::fixed('a', 10, 10), Anchor::TopLeft, Anchor::TopLeft),
        ];
    }

    #[DataProvider('builders')]
    public function testExposesItsIdWithoutBuilding(ContainerBuilder $builder): void
    {
        self::assertSame('c', $builder->id());
    }

    #[DataProvider('builders')]
    public function testIsUsableAsANodeWithoutAnExplicitBuild(ContainerBuilder $builder): void
    {
        self::assertInstanceOf(LayoutNodeInterface::class, $builder);
        $context = new LayoutContext();
        $constraints = BoxConstraints::tight(100, 100);
        // measure() and solve() must delegate to the built node, so calling them
        // on the builder and on its build() result cannot diverge.
        self::assertEquals(
            $builder->build()->measure($context, $constraints),
            $builder->measure($context, $constraints),
        );
        self::assertEquals(
            $builder->build()->solve($context, Rect::fromSize(100, 100))->frame,
            $builder->solve($context, Rect::fromSize(100, 100))->frame,
        );
    }

    #[DataProvider('builders')]
    public function testPaddingIsFluentAndReachesTheBuiltNode(ContainerBuilder $builder): void
    {
        $bare = $builder->solve(new LayoutContext(), Rect::fromSize(100, 100));
        self::assertSame($builder, $builder->padding(InsetSpec::px(10)));
        $padded = $builder->solve(new LayoutContext(), Rect::fromSize(100, 100));
        // The container keeps the rect it was solved into; only its content
        // moves inwards by the padding.
        self::assertEquals($bare->frame, $padded->frame);
        self::assertSame(0.0, $bare->frameOf('a')?->x);
        self::assertSame(0.0, $bare->frameOf('a')?->y);
        self::assertSame(10.0, $padded->frameOf('a')?->x);
        self::assertSame(10.0, $padded->frameOf('a')?->y);
    }
}
