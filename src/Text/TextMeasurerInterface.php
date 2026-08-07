<?php

declare(strict_types=1);

namespace Atelier\Layout\Text;

interface TextMeasurerInterface
{
    public function measureLine(string $text, float $fontSize, FontWeight $weight = FontWeight::Normal): TextMetrics;

    public function wrap(string $text, float $maxWidth, float $fontSize, float $lineHeight = 1.2, bool $breakWords = false, FontWeight $weight = FontWeight::Normal): TextBlockMetrics;
}
