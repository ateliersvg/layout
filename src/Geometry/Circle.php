<?php

declare(strict_types=1);

namespace Atelier\Layout\Geometry;

use Atelier\Layout\Exception\InvalidArgumentException;

final readonly class Circle
{
    public function __construct(
        public Point $center,
        public float $radius,
    ) {
        if ($radius < 0.0) {
            throw new InvalidArgumentException(sprintf('Circle radius must not be negative, got %s.', $radius));
        }
    }

    public function boundingRect(): Rect
    {
        return new Rect(
            $this->center->x - $this->radius,
            $this->center->y - $this->radius,
            2.0 * $this->radius,
            2.0 * $this->radius,
        );
    }

    public function innerRadius(
        Insets $padding = new Insets(0.0, 0.0, 0.0, 0.0),
        float $strokeWidth = 0.0,
        StrokePlacement $strokePlacement = StrokePlacement::Center,
    ): float {
        if ($strokeWidth < 0.0) {
            throw new InvalidArgumentException(sprintf('Stroke width must not be negative, got %s.', $strokeWidth));
        }

        return max(0.0, $this->radius - max($padding->top, $padding->right, $padding->bottom, $padding->left) - $strokePlacement->innerInset($strokeWidth));
    }

    public function safeSquare(
        Insets $padding = new Insets(0.0, 0.0, 0.0, 0.0),
        float $strokeWidth = 0.0,
        StrokePlacement $strokePlacement = StrokePlacement::Center,
    ): Rect {
        $innerRadius = $this->innerRadius($padding, $strokeWidth, $strokePlacement);
        $side = sqrt(2.0) * $innerRadius;

        return new Rect(
            $this->center->x - $side / 2.0,
            $this->center->y - $side / 2.0,
            $side,
            $side,
        );
    }
}
