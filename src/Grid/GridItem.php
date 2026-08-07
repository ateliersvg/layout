<?php

declare(strict_types=1);

namespace Atelier\Layout\Grid;

use Atelier\Layout\Alignment;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\LayoutNodeInterface;

final readonly class GridItem
{
    public function __construct(
        public LayoutNodeInterface $node,
        public int $columnSpan = 1,
        public int $rowSpan = 1,
        public ?Alignment $alignX = null,
        public ?Alignment $alignY = null,
    ) {
        if ($columnSpan < 1 || $rowSpan < 1) {
            throw new InvalidArgumentException('Grid item spans must be positive.');
        }
    }
}
