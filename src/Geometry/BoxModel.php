<?php

declare(strict_types=1);

namespace Atelier\Layout\Geometry;

use Atelier\Layout\Exception\InvalidArgumentException;

final readonly class BoxModel
{
    public function __construct(
        public Rect $outer,
        public Insets $padding = new Insets(0.0, 0.0, 0.0, 0.0),
        public float $strokeWidth = 0.0,
        public StrokePlacement $strokePlacement = StrokePlacement::Center,
    ) {
        if ($strokeWidth < 0.0) {
            throw new InvalidArgumentException(sprintf('Stroke width must not be negative, got %s.', $strokeWidth));
        }
    }

    public function strokeInset(): float
    {
        return $this->strokePlacement->innerInset($this->strokeWidth);
    }

    public function contentRect(): Rect
    {
        $strokeInset = $this->strokeInset();

        return $this->outer->inset(new Insets(
            $this->padding->top + $strokeInset,
            $this->padding->right + $strokeInset,
            $this->padding->bottom + $strokeInset,
            $this->padding->left + $strokeInset,
        ));
    }
}
