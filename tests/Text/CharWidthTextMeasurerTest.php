<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Text;

use Atelier\Layout\Text\CharWidthTextMeasurer;
use Atelier\Layout\Text\FontWeight;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CharWidthTextMeasurer::class)]
#[CoversClass(FontWeight::class)]
final class CharWidthTextMeasurerTest extends TestCase
{
    public function testMeasureLineScalesWithFontSize(): void
    {
        $measurer = new CharWidthTextMeasurer();
        $small = $measurer->measureLine('Hello', 10);
        $large = $measurer->measureLine('Hello', 20);

        $this->assertEqualsWithDelta($small->width * 2.0, $large->width, 0.001);
        $this->assertSame(12.0, $small->height);
        $this->assertSame(8.0, $small->ascent);
    }

    public function testMeasureLineAccountsForBoldWeight(): void
    {
        $measurer = new CharWidthTextMeasurer();
        $normal = $measurer->measureLine('Label', 12);
        $bold = $measurer->measureLine('Label', 12, FontWeight::Bold);

        $this->assertGreaterThan($normal->width, $bold->width);
        $this->assertSame($normal->height, $bold->height);
        $this->assertSame($normal->ascent, $bold->ascent);
    }

    public function testMetricFactorsCanBeConfigured(): void
    {
        $measurer = new CharWidthTextMeasurer(heightFactor: 1.4, ascentFactor: 0.92);
        $metrics = $measurer->measureLine('Hello', 10);

        $this->assertSame(14.0, $metrics->height);
        $this->assertEqualsWithDelta(9.2, $metrics->ascent, 1e-9);
    }

    public function testWrapSplitsLongText(): void
    {
        $measurer = new CharWidthTextMeasurer();
        $block = $measurer->wrap('The quick brown fox jumps', 70, 12);

        $this->assertGreaterThan(1, \count($block->lines));
        $this->assertGreaterThan(0.0, $block->firstBaseline);
        $this->assertGreaterThanOrEqual($block->firstBaseline, $block->lastBaseline);
    }

    public function testWrapReturnsEmptyBlockForBlankText(): void
    {
        $measurer = new CharWidthTextMeasurer();
        $block = $measurer->wrap('   ', 100, 12);

        $this->assertSame([], $block->lines);
        $this->assertSame(0.0, $block->width);
        $this->assertSame(0.0, $block->height);
        $this->assertSame(0.0, $block->firstBaseline);
        $this->assertSame(0.0, $block->lastBaseline);
    }

    public function testWrapReturnsEmptyBlockForNonPositiveWidth(): void
    {
        $measurer = new CharWidthTextMeasurer();
        $block = $measurer->wrap('Hello', 0, 12);

        $this->assertSame([], $block->lines);
    }

    public function testWrapUsesBoldWeightForLineWidths(): void
    {
        $measurer = new CharWidthTextMeasurer();
        $normal = $measurer->wrap('Alpha beta', 100, 12);
        $bold = $measurer->wrap('Alpha beta', 100, 12, weight: FontWeight::Bold);

        $this->assertGreaterThan($normal->width, $bold->width);
    }
}
