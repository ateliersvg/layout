<?php

declare(strict_types=1);

namespace Atelier\Layout\Geometry;

use Atelier\Layout\Anchor;
use Atelier\Layout\Exception\InvalidArgumentException;

final readonly class Rect
{
    public function __construct(
        public float $x,
        public float $y,
        public float $width,
        public float $height,
    ) {
        if ($width < 0.0 || $height < 0.0) {
            throw new InvalidArgumentException(sprintf('Rect dimensions must not be negative, got %s x %s.', $width, $height));
        }
    }

    public static function fromSize(float $width, float $height): self
    {
        return new self(0.0, 0.0, $width, $height);
    }

    public function right(): float
    {
        return $this->x + $this->width;
    }

    public function bottom(): float
    {
        return $this->y + $this->height;
    }

    public function size(): Size
    {
        return new Size($this->width, $this->height);
    }

    /**
     * True when the other rectangle lies wholly inside this one.
     *
     * The tolerance absorbs the sub-pixel drift that accumulates through
     * percentage insets and fractional tracks, so a child that lands exactly on
     * an edge is not reported as sticking out.
     */
    public function containsRect(self $other, float $tolerance = 1e-9): bool
    {
        return $other->x >= $this->x - $tolerance
            && $other->y >= $this->y - $tolerance
            && $other->right() <= $this->right() + $tolerance
            && $other->bottom() <= $this->bottom() + $tolerance;
    }

    public function inset(Insets $insets): self
    {
        return new self(
            $this->x + $insets->left,
            $this->y + $insets->top,
            max(0.0, $this->width - $insets->horizontal()),
            max(0.0, $this->height - $insets->vertical()),
        );
    }

    public function pointAt(Anchor $anchor): Point
    {
        return match ($anchor) {
            Anchor::TopLeft => new Point($this->x, $this->y),
            Anchor::TopCenter => new Point($this->x + $this->width / 2.0, $this->y),
            Anchor::TopRight => new Point($this->right(), $this->y),
            Anchor::CenterLeft => new Point($this->x, $this->y + $this->height / 2.0),
            Anchor::Center => new Point($this->x + $this->width / 2.0, $this->y + $this->height / 2.0),
            Anchor::CenterRight => new Point($this->right(), $this->y + $this->height / 2.0),
            Anchor::BottomLeft => new Point($this->x, $this->bottom()),
            Anchor::BottomCenter => new Point($this->x + $this->width / 2.0, $this->bottom()),
            Anchor::BottomRight => new Point($this->right(), $this->bottom()),
        };
    }
}
