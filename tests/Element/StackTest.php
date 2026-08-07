<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Distribution;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Spacer;
use Atelier\Layout\Element\Stack;
use Atelier\Layout\Element\TextBlock;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Text\FontWeight;
use Atelier\Layout\Value\InsetSpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Stack::class)]
#[CoversClass(Frame::class)]
#[CoversClass(Spacer::class)]
#[CoversClass(TextBlock::class)]
final class StackTest extends TestCase
{
    public function testHorizontalStackResolvesPaddingGapAndAlignment(): void
    {
        $stack = Stack::row('toolbar')
            ->padding(InsetSpec::percent(4))
            ->gap(10)
            ->alignItems(Alignment::Center)
            ->add(Frame::fixed('left', 40, 20))
            ->add(Frame::fixed('right', 50, 20));

        $result = $stack->solve(new LayoutContext(), Rect::fromSize(200, 80));
        $left = $result->frameOf('left');
        $right = $result->frameOf('right');

        $this->assertNotNull($left);
        $this->assertNotNull($right);
        $this->assertSame(8.0, $left->x);
        $this->assertEqualsWithDelta(30.0, $left->y, 0.001);
        $this->assertSame(58.0, $right->x);
    }

    public function testSpaceBetweenDistributionUsesFreeSpaceBetweenChildren(): void
    {
        $stack = Stack::row('row')
            ->distribute(Distribution::SpaceBetween)
            ->add(Frame::fixed('a', 10, 10))
            ->add(Frame::fixed('b', 10, 10))
            ->add(Frame::fixed('c', 10, 10));

        $result = $stack->solve(new LayoutContext(), Rect::fromSize(100, 10));

        $this->assertSame(0.0, $result->frameOf('a')?->x);
        $this->assertSame(45.0, $result->frameOf('b')?->x);
        $this->assertSame(90.0, $result->frameOf('c')?->x);
    }

    public function testFlexibleSpacerConsumesFreeMainAxisSpace(): void
    {
        $stack = Stack::row('row')
            ->gap(4)
            ->add(Frame::fixed('start', 20, 20))
            ->add(new Spacer('space'))
            ->add(Frame::fixed('end', 20, 20));

        $result = $stack->solve(new LayoutContext(), Rect::fromSize(100, 20));

        $this->assertSame(24.0, $result->frameOf('space')?->x);
        $this->assertSame(52.0, $result->frameOf('space')?->width);
        $this->assertSame(80.0, $result->frameOf('end')?->x);
    }

    public function testHorizontalStackMeasureSumsMainAxisAndTakesCrossMaximum(): void
    {
        $stack = Stack::row('row')
            ->gap(4)
            ->add(Frame::fixed('a', 30, 20))
            ->add(Frame::fixed('b', 40, 10));

        $measure = $stack->measure(new LayoutContext(), BoxConstraints::unconstrained());

        $this->assertSame(74.0, $measure->size->width);
        $this->assertSame(20.0, $measure->size->height);
    }

    public function testVerticalStackMeasureAddsPaddingWhenConstrained(): void
    {
        $stack = Stack::column('col')
            ->padding(InsetSpec::px(10))
            ->gap(5)
            ->add(Frame::fixed('a', 30, 20))
            ->add(Frame::fixed('b', 40, 10));

        $measure = $stack->measure(new LayoutContext(), new BoxConstraints(maxWidth: 200, maxHeight: 200));

        $this->assertSame(60.0, $measure->size->width);
        $this->assertSame(55.0, $measure->size->height);
    }

    public function testVerticalStackSolveStacksChildrenAlongMainAxis(): void
    {
        $stack = Stack::column('col')
            ->gap(5)
            ->add(Frame::fixed('a', 30, 20))
            ->add(Frame::fixed('b', 40, 10));

        $result = $stack->solve(new LayoutContext(), Rect::fromSize(100, 100));

        $this->assertSame(0.0, $result->frameOf('a')?->x);
        $this->assertSame(0.0, $result->frameOf('a')?->y);
        $this->assertSame(30.0, $result->frameOf('a')?->width);
        $this->assertSame(20.0, $result->frameOf('a')?->height);
        $this->assertSame(25.0, $result->frameOf('b')?->y);
        $this->assertSame(10.0, $result->frameOf('b')?->height);
    }

    public function testSpaceAroundDistributionWrapsEqualGutters(): void
    {
        $stack = Stack::row('row')
            ->distribute(Distribution::SpaceAround)
            ->add(Frame::fixed('a', 10, 10))
            ->add(Frame::fixed('b', 10, 10));

        $result = $stack->solve(new LayoutContext(), Rect::fromSize(100, 10));

        $this->assertSame(20.0, $result->frameOf('a')?->x);
        $this->assertSame(70.0, $result->frameOf('b')?->x);
    }

    public function testEmptyStackSolvesToItsRect(): void
    {
        $result = Stack::column('empty')->solve(new LayoutContext(), Rect::fromSize(50, 40));

        $this->assertSame('empty', $result->id);
        $this->assertSame(50.0, $result->frame->width);
        $this->assertSame(40.0, $result->frame->height);
        $this->assertSame([], $result->children);
    }

    public function testStackAndSpacerExposeTheirIds(): void
    {
        $this->assertSame('row', Stack::row('row')->id());
        $this->assertSame('space', (new Spacer('space'))->id());
    }

    public function testHorizontalStackMeasuresTextBlockUnconstrainedOnMainAxis(): void
    {
        $text = 'The quick brown fox jumps over the lazy dog and keeps running for a while';
        $block = TextBlock::of('label', $text, 14.0);
        $context = new LayoutContext();

        $expected = $block->measure($context, BoxConstraints::unconstrained());

        $stack = Stack::row('row')->add($block);
        $result = $stack->solve($context, Rect::fromSize(100, 40));

        $this->assertSame($expected->size->width, $result->frameOf('label')?->width);
    }

    public function testBoxPreferredAndStretchReportConstraintsAndFlex(): void
    {
        $context = new LayoutContext();

        $preferred = Frame::preferred('p', 30, 20, 5, 5);
        $preferredMeasure = $preferred->measure($context, BoxConstraints::unconstrained());
        $this->assertSame(30.0, $preferredMeasure->size->width);
        $this->assertSame(20.0, $preferredMeasure->size->height);

        $stretch = Frame::stretch('s', 2.0);
        $stretchMeasure = $stretch->measure($context, BoxConstraints::unconstrained());
        $this->assertSame(2.0, $stretch->flex());
        $this->assertSame(0.0, $stretchMeasure->size->width);
        $this->assertSame(0.0, $stretchMeasure->size->height);
    }

    public function testTheBuiltStackCarriesTheIdThroughBuild(): void
    {
        // The entry point returns a builder, so the node's own id()
        // is only reachable past build().
        self::assertSame('toolbar', Stack::column('toolbar')->build()->id());
    }

    public function testAlignToBaselineSitsChildrenOnACommonLine(): void
    {
        $context = new LayoutContext();

        $result = Stack::row('row')
            ->gap(8)
            ->alignToBaseline()
            ->add(TextBlock::of('big', 'Ag', 24))
            ->add(TextBlock::of('small', 'Ag', 10))
            ->solve($context, Rect::fromSize(300, 60));

        $big = $result->frameOf('big');
        $small = $result->frameOf('small');
        self::assertNotNull($big);
        self::assertNotNull($small);

        $baselineOf = static function (string $id, float $fontSize) use ($context, $result): float {
            $frame = $result->frameOf($id);
            self::assertNotNull($frame);

            return $frame->y + $context->textMeasurer->measureLine('Ag', $fontSize, FontWeight::Normal)->ascent;
        };

        self::assertEqualsWithDelta($baselineOf('big', 24), $baselineOf('small', 10), 1e-9);
        // The taller child keeps the top edge; only the shorter one moves down.
        self::assertSame(0.0, $big->y);
        self::assertGreaterThan(0.0, $small->y);
    }

    public function testAlignToBaselineLeavesChildrenWithoutABaselineOnTheTopEdge(): void
    {
        $result = Stack::row('row')
            ->alignToBaseline()
            ->add(TextBlock::of('text', 'Ag', 24))
            ->add(Frame::fixed('box', 20, 20))
            ->solve(new LayoutContext(), Rect::fromSize(300, 60));

        self::assertSame(0.0, $result->frameOf('box')?->y);
    }

    public function testAlignToBaselineIsRejectedOnAVerticalStack(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Stack::column('col')
            ->alignToBaseline()
            ->add(TextBlock::of('text', 'Ag', 12))
            ->build();
    }

    public function testStretchAlignmentGivesChildrenTheFullCrossSize(): void
    {
        $result = Stack::row('row')
            ->gap(10)
            ->alignItems(Alignment::Stretch)
            ->add(Frame::fixed('a', 20, 12))
            ->add(Frame::fixed('b', 20, 30))
            ->solve(new LayoutContext(), Rect::fromSize(200, 80));

        // Stretch overrides each child's own cross measure.
        self::assertSame(80.0, $result->frameOf('a')?->height);
        self::assertSame(80.0, $result->frameOf('b')?->height);
    }
}
