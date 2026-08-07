<?php

declare(strict_types=1);

namespace Atelier\Layout;

use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Result\PlacedNode;

interface LayoutNodeInterface
{
    public function id(): string;

    public function measure(LayoutContext $context, BoxConstraints $constraints): IntrinsicSize;

    public function solve(LayoutContext $context, Rect $rect): PlacedNode;
}
