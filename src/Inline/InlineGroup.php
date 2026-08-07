<?php

declare(strict_types=1);

namespace Atelier\Layout\Inline;

use Atelier\Layout\Alignment;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Value\InsetSpec;

final readonly class InlineGroup
{
    /**
     * @param list<array{id: string, preferredWidth: float|null, minWidth: float, fixedWidth: float|null}> $items
     */
    private function __construct(
        private string $id,
        private bool $equalWidths,
        private array $items = [],
        private float $gap = 0.0,
        private ?InsetSpec $padding = null,
        private Alignment $align = Alignment::Start,
    ) {
        if ($gap < 0.0) {
            throw new InvalidArgumentException('Item row gap must not be negative.');
        }
    }

    public static function equal(string $id): self
    {
        return new self($id, true);
    }

    public static function contentSized(string $id): self
    {
        return new self($id, false);
    }

    public function gap(float $gap): self
    {
        return new self($this->id, $this->equalWidths, $this->items, $gap, $this->padding, $this->align);
    }

    public function padding(InsetSpec $padding): self
    {
        return new self($this->id, $this->equalWidths, $this->items, $this->gap, $padding, $this->align);
    }

    public function align(Alignment $align): self
    {
        return new self($this->id, $this->equalWidths, $this->items, $this->gap, $this->padding, $align);
    }

    public function add(string $id, ?float $preferredWidth = null, float $minWidth = 0.0, ?float $fixedWidth = null): self
    {
        if ($minWidth < 0.0 || (null !== $preferredWidth && $preferredWidth < 0.0) || (null !== $fixedWidth && $fixedWidth < 0.0)) {
            throw new InvalidArgumentException('Item row widths must not be negative.');
        }

        return new self($this->id, $this->equalWidths, [...$this->items, ['id' => $id, 'preferredWidth' => $preferredWidth, 'minWidth' => $minWidth, 'fixedWidth' => $fixedWidth]], $this->gap, $this->padding, $this->align);
    }

    public function place(Rect $frame): PlacedInlineGroup
    {
        $available = $frame->inset($this->paddingSpec()->resolve($frame->size()));
        $count = \count($this->items);
        $gaps = max(0, $count - 1) * $this->gap;
        $widths = [];
        $overflowX = false;

        if (0 === $count) {
            return new PlacedInlineGroup($this->id, $available, [], false);
        }

        if ($this->equalWidths) {
            $itemWidth = max(0.0, ($available->width - $gaps) / $count);
            $widths = array_fill(0, $count, $itemWidth);
        } else {
            $contentWidth = 0.0;
            foreach ($this->items as $item) {
                $width = $item['fixedWidth'] ?? max($item['minWidth'], $item['preferredWidth'] ?? $item['minWidth']);
                $widths[] = $width;
                $contentWidth += $width;
            }

            $overflowX = $contentWidth + $gaps > $available->width;
        }

        $rowWidth = \array_sum($widths) + $gaps;
        $offset = $this->alignOffset($available->x, $available->width, $rowWidth);
        $x = $overflowX ? $available->x : $offset;
        $items = [];
        $cursor = $x;
        foreach ($this->items as $index => $item) {
            $itemWidth = $widths[$index] ?? 0.0;
            $items[] = new PlacedInlineItem($item['id'], new Rect($cursor, $available->y, $itemWidth, $available->height));
            $cursor += $itemWidth + $this->gap;
        }

        return new PlacedInlineGroup($this->id, new Rect($x, $available->y, $rowWidth, $available->height), $items, $overflowX);
    }

    private function paddingSpec(): InsetSpec
    {
        return $this->padding ?? InsetSpec::zero();
    }

    private function alignOffset(float $origin, float $available, float $used): float
    {
        $free = max(0.0, $available - $used);

        return match ($this->align) {
            Alignment::Start, Alignment::Stretch => $origin,
            Alignment::Center => $origin + $free / 2.0,
            Alignment::End => $origin + $free,
        };
    }
}
