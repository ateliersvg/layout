<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Element\TextBlock;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Text\CharWidthTextMeasurer;
use Atelier\Layout\Text\FontWeight;
use Atelier\Layout\Text\TextLayout;
use Atelier\Layout\Text\TextLineLayout;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextBlock::class)]
#[CoversClass(TextLayout::class)]
#[CoversClass(TextLineLayout::class)]
#[CoversClass(CharWidthTextMeasurer::class)]
#[CoversClass(FontWeight::class)]
final class TextBlockTest extends TestCase
{
    public function testTextLayoutReturnsLineFramesAndBaselines(): void
    {
        $layout = TextBlock::of('label', 'Hello', 10)
            ->align(Alignment::Center, Alignment::Center)
            ->layout(new LayoutContext(), new Rect(10, 20, 100, 50));

        $this->assertCount(1, $layout->lines);
        $line = $layout->lines[0];
        $this->assertSame('Hello', $line->text);
        $this->assertGreaterThan(10.0, $line->frame->x);
        $this->assertSame(39.0, $line->frame->y);
        $this->assertSame(47.0, $line->baseline);
        $this->assertSame(47.0, $layout->firstBaseline());
        $this->assertSame(47.0, $layout->lastBaseline());
        $this->assertFalse($layout->hasOverflow());
    }

    public function testTextLayoutCanSnapMultilineTextToBottom(): void
    {
        $layout = TextBlock::of('label', 'Alpha beta gamma delta', 10)
            ->align(Alignment::Start, Alignment::End)
            ->layout(new LayoutContext(), new Rect(0, 0, 45, 60));

        $this->assertGreaterThan(1, \count($layout->lines));
        $this->assertSame(60.0, $layout->lines[\count($layout->lines) - 1]->frame->bottom());
    }

    public function testTextLayoutReportsVerticalOverflow(): void
    {
        $layout = TextBlock::of('label', 'Alpha beta gamma delta epsilon zeta', 10)
            ->layout(new LayoutContext(), new Rect(0, 0, 45, 20));

        $this->assertTrue($layout->overflowY);
        $this->assertTrue($layout->hasOverflow());
    }

    public function testTextLayoutCanBreakLongWords(): void
    {
        $layout = TextBlock::of('label', 'Supercalifragilistic', 10)
            ->breakWords()
            ->layout(new LayoutContext(), new Rect(0, 0, 30, 100));

        $this->assertGreaterThan(1, \count($layout->lines));
        $this->assertFalse($layout->overflowX);
    }

    public function testTextLayoutCanUseBoldWeight(): void
    {
        $normal = TextBlock::of('label', 'Label', 10)
            ->layout(new LayoutContext(), new Rect(0, 0, 100, 40));
        $bold = TextBlock::of('label', 'Label', 10)
            ->weight(FontWeight::Bold)
            ->layout(new LayoutContext(), new Rect(0, 0, 100, 40));

        $this->assertGreaterThan($normal->lines[0]->frame->width, $bold->lines[0]->frame->width);
    }

    public function testMeasureWrapsTextWhenWidthIsConstrained(): void
    {
        $measure = TextBlock::of('label', 'Hello', 10)
            ->measure(new LayoutContext(), new BoxConstraints(maxWidth: 100, maxHeight: 100));

        $this->assertEqualsWithDelta(22.78, $measure->size->width, 0.001);
        $this->assertSame(12.0, $measure->size->height);
        $this->assertSame(8.0, $measure->firstBaseline);
        $this->assertSame(8.0, $measure->lastBaseline);
    }

    public function testMeasureUsesSingleLineMetricsWhenUnconstrained(): void
    {
        $measure = TextBlock::of('label', 'Hello', 10)
            ->measure(new LayoutContext(), BoxConstraints::unconstrained());

        $this->assertEqualsWithDelta(22.78, $measure->size->width, 0.001);
        $this->assertSame(12.0, $measure->size->height);
        $this->assertSame(8.0, $measure->firstBaseline);
        $this->assertSame(8.0, $measure->lastBaseline);
    }

    public function testLineHeightScalesMeasuredBlockHeight(): void
    {
        $measure = TextBlock::of('label', 'Hello', 10)
            ->lineHeight(2.0)
            ->measure(new LayoutContext(), new BoxConstraints(maxWidth: 100));

        $this->assertSame(20.0, $measure->size->height);
    }

    public function testSolveReturnsRectAndMeasuredSize(): void
    {
        $solved = TextBlock::of('label', 'Hello', 10)
            ->solve(new LayoutContext(), new Rect(5, 5, 100, 40));

        $this->assertSame(5.0, $solved->frame->x);
        $this->assertSame(100.0, $solved->frame->width);
        $this->assertSame(40.0, $solved->frame->height);
        $this->assertEqualsWithDelta(22.78, $solved->measure->size->width, 0.001);
        $this->assertSame(12.0, $solved->measure->size->height);
    }

    public function testTextBlockExposesItsId(): void
    {
        $this->assertSame('label', TextBlock::of('label', 'Hello')->id());
    }

    public function testMaxLinesDropsExtraLinesAndReportsTruncation(): void
    {
        $context = new LayoutContext();
        $rect = new Rect(0, 0, 60, 200);
        $text = 'one two three four five six seven eight';

        $full = TextBlock::of('label', $text, 10)->layout($context, $rect);
        $capped = TextBlock::of('label', $text, 10)->maxLines(2)->layout($context, $rect);

        self::assertGreaterThan(2, \count($full->lines));
        self::assertFalse($full->isTruncated());

        self::assertCount(2, $capped->lines);
        self::assertTrue($capped->isTruncated());
        // The kept lines are the first ones, unchanged.
        self::assertSame($full->lines[0]->text, $capped->lines[0]->text);
        self::assertSame($full->lines[1]->text, $capped->lines[1]->text);
    }

    public function testMaxLinesLeavesAShortTextAlone(): void
    {
        $layout = TextBlock::of('label', 'short', 10)
            ->maxLines(4)
            ->layout(new LayoutContext(), new Rect(0, 0, 200, 200));

        self::assertCount(1, $layout->lines);
        self::assertFalse($layout->isTruncated());
    }

    public function testTruncatedTextAlignsOnTheHeightItActuallyUses(): void
    {
        $context = new LayoutContext();
        $rect = new Rect(0, 0, 60, 200);
        $text = 'one two three four five six seven eight';

        $layout = TextBlock::of('label', $text, 10)
            ->maxLines(2)
            ->align(Alignment::Start, Alignment::End)
            ->layout($context, $rect);

        // Aligning on the full block height would push the visible lines below
        // the rect by the height of everything that was dropped.
        $lastLine = $layout->lines[\count($layout->lines) - 1];
        self::assertLessThanOrEqual($rect->bottom() + 1e-9, $lastLine->frame->bottom());
    }

    public function testMaxLinesRejectsZeroAndNegativeCaps(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TextBlock::of('label', 'text', 10)->maxLines(0);
    }

    public function testMaxLinesCapsTheIntrinsicHeight(): void
    {
        $context = new LayoutContext();
        $constraints = new BoxConstraints(maxWidth: 60);
        $text = 'one two three four five six seven eight';

        $full = TextBlock::of('label', $text, 10)->measure($context, $constraints);
        $capped = TextBlock::of('label', $text, 10)->maxLines(2)->measure($context, $constraints);

        self::assertGreaterThan($capped->size->height, $full->size->height);
        self::assertSame(2 * 10.0 * 1.2, $capped->size->height);
        // The last baseline is dropped: it belonged to a line that is gone.
        self::assertNull($capped->lastBaseline);
        self::assertNotNull($capped->firstBaseline);
    }
}
