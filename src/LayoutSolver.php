<?php

declare(strict_types=1);

namespace Atelier\Layout;

use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Result\PlacedTree;

final readonly class LayoutSolver
{
    public function __construct(
        private LayoutContext $context = new LayoutContext(),
    ) {
    }

    public function solve(LayoutNodeInterface $node, Rect $rect): PlacedTree
    {
        return new PlacedTree($node->solve($this->context, $rect));
    }
}
