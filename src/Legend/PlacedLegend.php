<?php

declare(strict_types=1);

namespace Atelier\Layout\Legend;

use Atelier\Layout\Geometry\Rect;

final readonly class PlacedLegend
{
    /**
     * @param list<PlacedLegendEntry> $entries
     */
    public function __construct(
        public string $id,
        public Rect $frame,
        public array $entries,
        public bool $overflowX,
        public bool $overflowY,
    ) {
    }

    public function entry(string $id): ?PlacedLegendEntry
    {
        foreach ($this->entries as $entry) {
            if ($entry->id === $id) {
                return $entry;
            }
        }

        return null;
    }
}
