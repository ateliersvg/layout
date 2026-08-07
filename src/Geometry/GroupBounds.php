<?php

declare(strict_types=1);

namespace Atelier\Layout\Geometry;

use Atelier\Layout\Exception\InvalidArgumentException;

final readonly class GroupBounds
{
    /**
     * @param array<string|int, Rect> $frames
     */
    public function __construct(
        private string $id,
        private array $frames = [],
        private ?Insets $padding = null,
        private float $labelTop = 0.0,
        private ?Size $minSize = null,
        private ?Rect $canvas = null,
    ) {
        if ($labelTop < 0.0) {
            throw new InvalidArgumentException('Group bounds top reserve must not be negative.');
        }
    }

    /**
     * @param array<string|int, Rect> $frames
     */
    public static function fromFrames(string $id, array $frames): self
    {
        return new self($id, $frames);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function padding(Insets $padding): self
    {
        return new self($this->id, $this->frames, $padding, $this->labelTop, $this->minSize, $this->canvas);
    }

    public function topReserve(float $height): self
    {
        if ($height < 0.0) {
            throw new InvalidArgumentException('Group bounds top reserve must not be negative.');
        }

        return new self($this->id, $this->frames, $this->padding, $height, $this->minSize, $this->canvas);
    }

    public function minSize(Size $size): self
    {
        return new self($this->id, $this->frames, $this->padding, $this->labelTop, $size, $this->canvas);
    }

    public function clampTo(Rect $canvas): self
    {
        return new self($this->id, $this->frames, $this->padding, $this->labelTop, $this->minSize, $canvas);
    }

    public function frame(): ?Rect
    {
        $frame = Bounds::fromRects($this->frames);
        if (null === $frame) {
            return null;
        }

        if (null !== $this->padding) {
            $frame = Bounds::expand($frame, $this->padding);
        }

        if (0.0 !== $this->labelTop) {
            $frame = new Rect(
                $frame->x,
                $frame->y - $this->labelTop,
                $frame->width,
                $frame->height + $this->labelTop,
            );
        }

        if (null !== $this->minSize) {
            $frame = new Rect(
                $frame->x,
                $frame->y,
                max($frame->width, $this->minSize->width),
                max($frame->height, $this->minSize->height),
            );
        }

        if (null !== $this->canvas) {
            $frame = $this->clamp($frame, $this->canvas);
        }

        return $frame;
    }

    private function clamp(Rect $frame, Rect $canvas): Rect
    {
        $x = max($frame->x, $canvas->x);
        $y = max($frame->y, $canvas->y);
        $right = min($frame->right(), $canvas->right());
        $bottom = min($frame->bottom(), $canvas->bottom());

        return new Rect(
            $x,
            $y,
            max(0.0, $right - $x),
            max(0.0, $bottom - $y),
        );
    }
}
