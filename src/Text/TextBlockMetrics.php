<?php

declare(strict_types=1);

namespace Atelier\Layout\Text;

final readonly class TextBlockMetrics
{
    /**
     * @param list<string> $lines
     */
    public function __construct(
        public array $lines,
        public float $width,
        public float $height,
        public float $firstBaseline,
        public float $lastBaseline,
    ) {
    }
}
