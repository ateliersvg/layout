<?php

declare(strict_types=1);

namespace Atelier\Layout\Value;

use Atelier\Layout\Exception\InvalidArgumentException;

final readonly class Length
{
    private function __construct(
        private float $value,
        private bool $percent,
    ) {
        if ($value < 0.0) {
            throw new InvalidArgumentException(sprintf('Length must not be negative, got %s.', $value));
        }
    }

    public static function px(float $value): self
    {
        return new self($value, false);
    }

    public static function percent(float $value): self
    {
        return new self($value, true);
    }

    public function place(float $reference): float
    {
        return $this->percent ? $reference * $this->value / 100.0 : $this->value;
    }
}
