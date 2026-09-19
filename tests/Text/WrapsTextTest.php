<?php

declare(strict_types=1);

namespace Atelier\Layout\Tests\Text;

use Atelier\Layout\Text\FontWeight;
use Atelier\Layout\Text\TextMeasurerInterface;
use Atelier\Layout\Text\TextMetrics;
use Atelier\Layout\Text\WrapsText;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

#[CoversTrait(WrapsText::class)]
final class WrapsTextTest extends TestCase
{
    public function testWrapsOnWordBoundaries(): void
    {
        // Five units a character, so six fit in thirty: one word a line.
        $block = $this->measurer()->wrap('one two three four', 30.0, 10.0);

        $this->assertSame(['one', 'two', 'three', 'four'], $block->lines);
    }

    public function testReportsTheWidestLine(): void
    {
        $block = $this->measurer()->wrap('one two three four', 30.0, 10.0);

        // 'three', the longest of the four, at five units a character.
        $this->assertEqualsWithDelta(25.0, $block->width, 0.001);
    }

    public function testStacksBaselinesByLineHeight(): void
    {
        $block = $this->measurer()->wrap('one two three four', 30.0, 10.0, 1.5);

        $this->assertEqualsWithDelta(8.0, $block->firstBaseline, 0.001);
        $this->assertEqualsWithDelta(8.0 + 3 * 15.0, $block->lastBaseline, 0.001);
        $this->assertEqualsWithDelta(4 * 15.0, $block->height, 0.001);
    }

    public function testTakesTheBaselineFromTheMeasurerRatherThanItsOwnState(): void
    {
        // A measurer with a different ascent must be followed, since the trait
        // only ever sees the interface.
        $block = $this->measurer(ascent: 0.5)->wrap('one', 30.0, 10.0);

        $this->assertEqualsWithDelta(5.0, $block->firstBaseline, 0.001);
    }

    public function testReturnsAnEmptyBlockForANonPositiveWidth(): void
    {
        $block = $this->measurer()->wrap('one two', 0.0, 10.0);

        $this->assertSame([], $block->lines);
        $this->assertEqualsWithDelta(0.0, $block->height, 0.001);
    }

    public function testReturnsAnEmptyBlockForBlankText(): void
    {
        $block = $this->measurer()->wrap("  \n ", 100.0, 10.0);

        $this->assertSame([], $block->lines);
    }

    public function testKeepsAWordTooLongToFitWhenBreakingIsOff(): void
    {
        $block = $this->measurer()->wrap('antidisestablishmentarianism', 30.0, 10.0);

        $this->assertSame(['antidisestablishmentarianism'], $block->lines);
    }

    public function testBreaksAWordTooLongToFitWhenBreakingIsOn(): void
    {
        $block = $this->measurer()->wrap('antidisestablishmentarianism', 30.0, 10.0, breakWords: true);

        $this->assertGreaterThan(1, \count($block->lines));
        $this->assertSame('antidisestablishmentarianism', implode('', $block->lines));
    }

    public function testMeasuresWithTheRequestedWeight(): void
    {
        // At forty units 'one two' fits when normal and does not when bold.
        $normal = $this->measurer()->wrap('one two three four', 40.0, 10.0);
        $bold = $this->measurer()->wrap('one two three four', 40.0, 10.0, weight: FontWeight::Bold);

        $this->assertSame(['one two', 'three', 'four'], $normal->lines);
        $this->assertSame(['one', 'two', 'three', 'four'], $bold->lines);
    }

    private function measurer(float $ascent = 0.8): TextMeasurerInterface
    {
        return new class($ascent) implements TextMeasurerInterface {
            use WrapsText;

            public function __construct(private readonly float $ascent)
            {
            }

            public function measureLine(string $text, float $fontSize, FontWeight $weight = FontWeight::Normal): TextMetrics
            {
                $factor = FontWeight::Bold === $weight ? 0.7 : 0.5;

                return new TextMetrics(
                    width: mb_strlen($text) * $factor * $fontSize,
                    height: $fontSize,
                    ascent: $this->ascent * $fontSize,
                );
            }
        };
    }
}
