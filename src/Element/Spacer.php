<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\FlexibleLayoutNodeInterface;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\IntrinsicSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\LayoutNodeInterface;
use Atelier\Layout\Result\PlacedNode;

final readonly class Spacer implements LayoutNodeInterface, FlexibleLayoutNodeInterface
{
    public function __construct(
        private string $id,
        private float $flex = 1.0,
        private float $minMainSize = 0.0,
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function flex(): float
    {
        return $this->flex;
    }

    public function measure(LayoutContext $context, BoxConstraints $constraints): IntrinsicSize
    {
        return new IntrinsicSize($constraints->constrain(new Size($this->minMainSize, 0.0)));
    }

    public function solve(LayoutContext $context, Rect $rect): PlacedNode
    {
        return new PlacedNode($this->id, $rect, new IntrinsicSize($rect->size()));
    }
}
