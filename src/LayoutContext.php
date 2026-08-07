<?php

declare(strict_types=1);

namespace Atelier\Layout;

use Atelier\Layout\Text\CharWidthTextMeasurer;
use Atelier\Layout\Text\TextMeasurerInterface;

final readonly class LayoutContext
{
    public function __construct(
        public TextMeasurerInterface $textMeasurer = new CharWidthTextMeasurer(),
        public ?float $snapStep = null,
    ) {
    }

    public function snap(float $value): float
    {
        if (null === $this->snapStep || $this->snapStep <= 0.0) {
            return $value;
        }

        return round($value / $this->snapStep) * $this->snapStep;
    }
}
