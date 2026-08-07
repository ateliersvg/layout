<?php

declare(strict_types=1);

namespace Atelier\Layout\Grid;

use Atelier\Layout\Geometry\Rect;

final readonly class GridSlot
{
    public function __construct(
        public string $id,
        public int $column,
        public int $row,
        public int $columnSpan,
        public int $rowSpan,
        public Rect $frame,
    ) {
    }
}
