<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\LayoutNodeInterface;

/**
 * A container builder whose children are a plain, ordered list of nodes
 * (Stack, Group). Defines add() once; placement-carrying containers (Grid,
 * Overlay) keep their own child storage and add() instead.
 */
abstract class NodeListBuilder extends ContainerBuilder
{
    /** @var list<LayoutNodeInterface> */
    protected array $children = [];

    /** Append a child node; a builder child is built on the spot so the tree stays immutable. */
    public function add(LayoutNodeInterface $child): static
    {
        $this->children[] = $child instanceof ContainerBuilder ? $child->build() : $child;

        return $this;
    }
}
