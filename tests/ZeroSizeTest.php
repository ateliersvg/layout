<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests;

use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Grid;
use Atelier\Layout\Element\Stack;
use Atelier\Layout\Element\TextBlock;
use Atelier\Layout\Fit\Fit;
use Atelier\Layout\Fit\FitMode;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\LayoutContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Zero in, zero out.
 *
 * A collapsed panel, an empty list, a track that received no space and a
 * percentage of nothing are ordinary states, not caller mistakes. They travel
 * through the stack without an exception and produce empty geometry.
 *
 * The point is not that nothing crashes: it is that nothing produces NaN. A
 * division by a zero dimension does not raise, it seeds a NaN that spreads
 * through every later computation and surfaces at the far end as coordinates
 * no renderer can draw, arbitrarily far from its cause.
 */
#[CoversClass(Fit::class)]
#[CoversClass(Grid::class)]
#[CoversClass(Stack::class)]
#[CoversClass(TextBlock::class)]
final class ZeroSizeTest extends TestCase
{
    public function testStackCollapsesIntoAnEmptyRect(): void
    {
        $result = Stack::row('row')
            ->gap(10)
            ->add(Frame::stretch('a'))
            ->add(Frame::fixed('b', 40, 20))
            ->solve(new LayoutContext(), Rect::fromSize(0, 0));

        foreach (['a', 'b'] as $id) {
            $frame = $result->frameOf($id);
            self::assertNotNull($frame);
            self::assertDrawable($frame);
        }
    }

    public function testGridSharesNothingBetweenItsTracks(): void
    {
        $result = Grid::tracks('g', [TrackSize::fr(), TrackSize::fr(), TrackSize::fr()])
            ->add(Frame::stretch('a'))
            ->add(Frame::stretch('b'))
            ->solve(new LayoutContext(), Rect::fromSize(0, 0));

        foreach (['a', 'b'] as $id) {
            $frame = $result->frameOf($id);
            self::assertNotNull($frame);
            self::assertSame(0.0, $frame->width);
            self::assertDrawable($frame);
        }
    }

    public function testTextInAZeroWidthRectProducesNoLines(): void
    {
        $layout = TextBlock::of('label', 'hello world', 10)
            ->layout(new LayoutContext(), new Rect(0, 0, 0, 50));

        self::assertSame([], $layout->lines);
    }

    /**
     * @return iterable<string, array{Size, Rect}>
     */
    public static function degenerateFits(): iterable
    {
        yield 'zero source' => [new Size(0, 0), new Rect(0, 0, 100, 100)];
        yield 'flat source' => [new Size(16, 0), new Rect(0, 0, 100, 100)];
        yield 'thin source' => [new Size(0, 9), new Rect(0, 0, 100, 100)];
        yield 'zero target' => [new Size(16, 9), new Rect(0, 0, 0, 0)];
        yield 'flat target' => [new Size(16, 9), new Rect(0, 0, 100, 0)];
    }

    #[DataProvider('degenerateFits')]
    public function testFitNeverDividesItselfIntoNaN(Size $source, Rect $target): void
    {
        foreach (FitMode::cases() as $mode) {
            self::assertDrawable(Fit::rect($source, $target, $mode), $mode->name);
        }
    }

    public function testFitReturnsEmptyGeometryRatherThanThrowingOnAZeroSource(): void
    {
        // Fit used to be the odd one out: it threw on a degenerate source while
        // every other primitive returned empty geometry.
        $fitted = Fit::rect(new Size(0, 0), new Rect(0, 0, 100, 100), FitMode::Contain);

        self::assertSame(0.0, $fitted->width);
        self::assertSame(0.0, $fitted->height);
    }

    private static function assertDrawable(Rect $rect, string $context = ''): void
    {
        foreach (['x' => $rect->x, 'y' => $rect->y, 'width' => $rect->width, 'height' => $rect->height] as $name => $value) {
            self::assertFalse(is_nan($value), sprintf('%s is NaN %s', $name, $context));
            self::assertTrue(is_finite($value), sprintf('%s is not finite %s', $name, $context));
        }
    }
}
