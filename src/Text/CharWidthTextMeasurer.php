<?php

declare(strict_types=1);

namespace Atelier\Layout\Text;

final class CharWidthTextMeasurer implements TextMeasurerInterface
{
    private const int EM_UNITS = 1000;
    private const int FALLBACK_WIDTH = 556;

    /**
     * @var array<int|string, int>
     */
    private const array CHAR_WIDTHS = [
        ' ' => 278, '!' => 278, '"' => 355, '#' => 556, '$' => 556, '%' => 889,
        '&' => 667, "'" => 191, '(' => 333, ')' => 333, '*' => 389, '+' => 584,
        ',' => 278, '-' => 333, '.' => 278, '/' => 278,
        '0' => 556, '1' => 556, '2' => 556, '3' => 556, '4' => 556,
        '5' => 556, '6' => 556, '7' => 556, '8' => 556, '9' => 556,
        ':' => 278, ';' => 278, '<' => 584, '=' => 584, '>' => 584,
        '?' => 556, '@' => 1015,
        'A' => 667, 'B' => 667, 'C' => 722, 'D' => 722, 'E' => 667, 'F' => 611,
        'G' => 778, 'H' => 722, 'I' => 278, 'J' => 500, 'K' => 667, 'L' => 556,
        'M' => 833, 'N' => 722, 'O' => 778, 'P' => 667, 'Q' => 778, 'R' => 722,
        'S' => 667, 'T' => 611, 'U' => 722, 'V' => 667, 'W' => 944, 'X' => 667,
        'Y' => 667, 'Z' => 611,
        '[' => 278, '\\' => 278, ']' => 278, '^' => 469, '_' => 556, '`' => 333,
        'a' => 556, 'b' => 556, 'c' => 500, 'd' => 556, 'e' => 556, 'f' => 278,
        'g' => 556, 'h' => 556, 'i' => 222, 'j' => 222, 'k' => 500, 'l' => 222,
        'm' => 833, 'n' => 556, 'o' => 556, 'p' => 556, 'q' => 556, 'r' => 333,
        's' => 500, 't' => 278, 'u' => 556, 'v' => 500, 'w' => 722, 'x' => 500,
        'y' => 500, 'z' => 500,
        '{' => 334, '|' => 260, '}' => 334, '~' => 584,
    ];

    public function __construct(
        private readonly float $heightFactor = 1.2,
        private readonly float $ascentFactor = 0.8,
        private readonly float $boldFactor = 1.05,
    ) {
    }

    public function measureLine(string $text, float $fontSize, FontWeight $weight = FontWeight::Normal): TextMetrics
    {
        $units = 0;
        foreach (mb_str_split($text) as $char) {
            $units += self::CHAR_WIDTHS[$char] ?? self::FALLBACK_WIDTH;
        }
        $width = $units * $fontSize / self::EM_UNITS;
        if (FontWeight::Bold === $weight) {
            $width *= $this->boldFactor;
        }

        return new TextMetrics(
            width: $width,
            height: $this->heightFactor * $fontSize,
            ascent: $this->ascentFactor * $fontSize,
        );
    }

    public function wrap(string $text, float $maxWidth, float $fontSize, float $lineHeight = 1.2, bool $breakWords = false, FontWeight $weight = FontWeight::Normal): TextBlockMetrics
    {
        if ($maxWidth <= 0.0 || '' === trim($text)) {
            return new TextBlockMetrics([], 0.0, 0.0, 0.0, 0.0);
        }

        $lines = [];
        $current = '';
        $words = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
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
            $lines = $this->breakLongLines($lines, $maxWidth, $fontSize, $weight);
        }

        $maxLineWidth = 0.0;
        foreach ($lines as $line) {
            $maxLineWidth = max($maxLineWidth, $this->measureLine($line, $fontSize, $weight)->width);
        }

        $lineBoxHeight = $fontSize * $lineHeight;
        $firstBaseline = $this->ascentFactor * $fontSize;
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
     * @param list<string> $lines
     *
     * @return list<string>
     */
    private function breakLongLines(array $lines, float $maxWidth, float $fontSize, FontWeight $weight): array
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
