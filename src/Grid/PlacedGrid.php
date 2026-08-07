<?php

declare(strict_types=1);

namespace Atelier\Layout\Grid;

use Atelier\Layout\Axis;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Result\PlacedTree;

final readonly class PlacedGrid
{
    /**
     * @param list<GridSlot>   $slots
     * @param list<Rect>       $columnFrames
     * @param list<Rect>       $rowFrames
     * @param list<list<Rect>> $slotFrames
     */
    public function __construct(
        public PlacedTree $result,
        public array $slots,
        public array $columnFrames,
        public array $rowFrames,
        public array $slotFrames,
    ) {
    }

    public function frameOf(string $id): ?Rect
    {
        return $this->result->frameOf($id);
    }

    public function column(int $index): ?Rect
    {
        return $this->columnFrames[$index] ?? null;
    }

    public function row(int $index): ?Rect
    {
        return $this->rowFrames[$index] ?? null;
    }

    public function slot(int $column, int $row): ?Rect
    {
        return $this->slotFrames[$row][$column] ?? null;
    }

    public function track(Axis $axis, int $index): ?Rect
    {
        return Axis::Horizontal === $axis ? $this->column($index) : $this->row($index);
    }

    /**
     * @return list<GridSlot>
     */
    public function slots(): array
    {
        return $this->slots;
    }

    public function item(string $id): ?GridSlot
    {
        foreach ($this->slots as $slot) {
            if ($slot->id === $id) {
                return $slot;
            }
        }

        return null;
    }

    public function namedArea(string $id): ?Rect
    {
        return $this->item($id)?->frame;
    }
}
