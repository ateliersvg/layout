<?php

declare(strict_types=1);

namespace Atelier\Layout\Text;

use Atelier\Layout\Geometry\Rect;

final readonly class TextLineLayout
{
    public function __construct(
        public string $text,
        public Rect $frame,
        public float $baseline,
    ) {
    }
}
