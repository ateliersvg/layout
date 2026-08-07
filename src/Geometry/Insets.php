<?php

declare(strict_types=1);

namespace Atelier\Layout\Geometry;

use Atelier\Layout\Exception\InvalidArgumentException;

final readonly class Insets
{
    public function __construct(
        public float $top,
        public float $right,
        public float $bottom,
        public float $left,
    ) {
        if ($top < 0.0 || $right < 0.0 || $bottom < 0.0 || $left < 0.0) {
            throw new InvalidArgumentException('Insets must not be negative.');
        }
    }

    public static function zero(): self
    {
        return new self(0.0, 0.0, 0.0, 0.0);
    }

    public static function all(float $value): self
    {
        return new self($value, $value, $value, $value);
    }

    public function horizontal(): float
    {
        return $this->left + $this->right;
    }

    public function vertical(): float
    {
        return $this->top + $this->bottom;
    }
}
