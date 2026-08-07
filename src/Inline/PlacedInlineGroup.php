<?php

declare(strict_types=1);

namespace Atelier\Layout\Inline;

use Atelier\Layout\Geometry\Rect;

final readonly class PlacedInlineGroup
{
    /**
     * @param list<PlacedInlineItem> $items
     */
    public function __construct(
        public string $id,
        public Rect $frame,
        public array $items,
        public bool $overflowX,
    ) {
    }

    public function item(string $id): ?PlacedInlineItem
    {
        foreach ($this->items as $item) {
            if ($item->id === $id) {
                return $item;
            }
        }

        return null;
    }
}
