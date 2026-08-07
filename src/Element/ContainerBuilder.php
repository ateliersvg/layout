<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\IntrinsicSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\LayoutNodeInterface;
use Atelier\Layout\Result\PlacedNode;
use Atelier\Layout\Value\InsetSpec;

/**
 * Shared, mutable builder surface for every container node.
 *
 * Holds only what is truly universal -- the id, padding, and the terminal
 * measure()/solve() that build then delegate. A builder is itself a
 * LayoutNodeInterface (id/measure/solve), so it drops in wherever a node is
 * expected and callers never need an explicit build(). Child storage differs per
 * container (plain nodes, grid items, anchored entries), so it lives in
 * subclasses; {@see NodeListBuilder} factors out the common plain-node case.
 */
abstract class ContainerBuilder implements LayoutNodeInterface
{
    protected ?InsetSpec $padding = null;

    public function __construct(protected readonly string $id)
    {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function padding(InsetSpec $padding): static
    {
        $this->padding = $padding;

        return $this;
    }

    abstract public function build(): LayoutNodeInterface;

    public function measure(LayoutContext $context, BoxConstraints $constraints): IntrinsicSize
    {
        return $this->build()->measure($context, $constraints);
    }

    public function solve(LayoutContext $context, Rect $rect): PlacedNode
    {
        return $this->build()->solve($context, $rect);
    }
}
