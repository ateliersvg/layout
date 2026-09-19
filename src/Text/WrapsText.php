<?php

declare(strict_types=1);

namespace Atelier\Layout\Text;

/**
 * Greedy line breaking, written once against TextMeasurerInterface.
 *
 * Wrapping needs to know how wide a line is, and nothing else about the font. A
 * measurer that implements the interface can take this trait and be left with the
 * one method only it can answer, measureLine().
 *
 * @phpstan-require-implements TextMeasurerInterface
 */
trait WrapsText
{
    public function wrap(string $text, float $maxWidth, float $fontSize, float $lineHeight = 1.2, bool $breakWords = false, FontWeight $weight = FontWeight::Normal): TextBlockMetrics
    {
        if ($maxWidth <= 0.0 || '' === trim($text)) {
            return new TextBlockMetrics([], 0.0, 0.0, 0.0, 0.0);
        }

        $lines = [];
        $current = '';
        $words = preg_split('/(\s+)/', $text, -1, \PREG_SPLIT_DELIM_CAPTURE);
        assert(false !== $words);

        foreach ($words as $word) {
            $candidate = $current.$word;
            if ($this->measureLine($candidate, $fontSize, $weight)->width <= $maxWidth || '' === $current) {
                if ($this->measureLine($candidate, $fontSize, $weight)->width <= $maxWidth || !$breakWords) {
                    $current = $candidate;
                    continue;
                }
            }

            if ('' !== trim($current)) {
                $lines[] = trim($current);
            }
            $current = preg_match('/^\s+$/', $word) ? '' : $word;
        }

        if ('' !== trim($current)) {
            $lines[] = trim($current);
        }

        if ($breakWords) {
            $lines = $this->splitLinesToWidth($lines, $maxWidth, $fontSize, $weight);
        }

        $maxLineWidth = 0.0;
        foreach ($lines as $line) {
            $maxLineWidth = max($maxLineWidth, $this->measureLine($line, $fontSize, $weight)->width);
        }

        $lineBoxHeight = $fontSize * $lineHeight;

        // Measured rather than read from a factor, because the trait only sees the
        // interface. 'M' rather than an empty string: a measurer backed by a real
        // font has no glyph to report an ascent for when there is no text.
        $firstBaseline = $this->measureLine('M', $fontSize, $weight)->ascent;
        $lastBaseline = [] === $lines ? 0.0 : $firstBaseline + (\count($lines) - 1) * $lineBoxHeight;

        return new TextBlockMetrics(
            lines: $lines,
            width: $maxLineWidth,
            height: \count($lines) * $lineBoxHeight,
            firstBaseline: $firstBaseline,
            lastBaseline: $lastBaseline,
        );
    }

    /**
     * Cuts lines that still exceed the width after word breaking, character by character.
     *
     * @param list<string> $lines
     *
     * @return list<string>
     */
    private function splitLinesToWidth(array $lines, float $maxWidth, float $fontSize, FontWeight $weight): array
    {
        $result = [];
        foreach ($lines as $line) {
            $current = '';
            foreach (mb_str_split($line) as $char) {
                $candidate = $current.$char;
                if ('' !== $current && $this->measureLine($candidate, $fontSize, $weight)->width > $maxWidth) {
                    $result[] = $current;
                    $current = $char;
                    continue;
                }
                $current = $candidate;
            }
            if ('' !== $current) {
                $result[] = $current;
            }
        }

        return $result;
    }
}
