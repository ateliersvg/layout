<?php

declare(strict_types=1);

namespace Atelier\Layout\Text;

final readonly class TextMetrics
{
    public function __construct(
        public float $width,
        public float $height,
        public float $ascent,
    ) {
    }
}
