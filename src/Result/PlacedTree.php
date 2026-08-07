<?php

declare(strict_types=1);

namespace Atelier\Layout\Result;

use Atelier\Layout\Anchor;
use Atelier\Layout\Geometry\Point;
use Atelier\Layout\Geometry\Rect;

final readonly class PlacedTree
{
    public function __construct(
        public PlacedNode $root,
    ) {
    }

    public function node(string $id): ?PlacedNode
    {
        return $this->root->find($id);
    }

    public function frameOf(string $id): ?Rect
    {
        return $this->root->frameOf($id);
    }

    public function anchorOf(string $id, Anchor $anchor): ?Point
    {
        return $this->frameOf($id)?->pointAt($anchor);
    }
}
