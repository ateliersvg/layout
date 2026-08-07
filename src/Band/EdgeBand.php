<?php

declare(strict_types=1);

namespace Atelier\Layout\Band;

use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Value\InsetSpec;

final readonly class EdgeBand
{
    private function __construct(
        private string $id,
        private bool $bandAtTop = true,
        private float $bandSize = 0.0,
        private float $gap = 0.0,
        private ?InsetSpec $padding = null,
    ) {
        if ($bandSize < 0.0 || $gap < 0.0) {
            throw new InvalidArgumentException('Edge band dimensions must not be negative.');
        }
    }

    public static function top(string $id): self
    {
        return new self($id, true);
    }

    public static function bottom(string $id): self
    {
        return new self($id, false);
    }

    public function bandSize(float $bandSize): self
    {
        return new self($this->id, $this->bandAtTop, $bandSize, $this->gap, $this->padding);
    }

    public function gap(float $gap): self
    {
        return new self($this->id, $this->bandAtTop, $this->bandSize, $gap, $this->padding);
    }

    public function padding(InsetSpec $padding): self
    {
        return new self($this->id, $this->bandAtTop, $this->bandSize, $this->gap, $padding);
    }

    public function place(Rect $available): PlacedEdgeBand
    {
        $content = $available->inset($this->paddingSpec()->resolve($available->size()));
        $bandSize = min($content->height, $this->bandSize);
        $contentHeight = max(0.0, $content->height - $bandSize - $this->gap);

        if ($this->bandAtTop) {
            $bandFrame = new Rect($content->x, $content->y, $content->width, $bandSize);
            $contentFrame = new Rect($content->x, $content->y + $bandSize + $this->gap, $content->width, $contentHeight);
        } else {
            $bandFrame = new Rect($content->x, $content->bottom() - $bandSize, $content->width, $bandSize);
            $contentFrame = new Rect($content->x, $content->y, $content->width, $contentHeight);
        }

        return new PlacedEdgeBand(
            $this->id,
            $content,
            $bandFrame,
            $contentFrame,
            $this->bandSize > $content->height,
            $this->bandAtTop,
        );
    }

    private function paddingSpec(): InsetSpec
    {
        return $this->padding ?? InsetSpec::zero();
    }
}
