<?php

declare(strict_types=1);

namespace Atelier\Layout\Result;

use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\IntrinsicSize;

final readonly class PlacedNode
{
    /**
     * @param list<PlacedNode> $children
     */
    public function __construct(
        public string $id,
        public Rect $frame,
        public IntrinsicSize $measure,
        public array $children = [],
    ) {
    }

    public function find(string $id): ?self
    {
        if ($this->id === $id) {
            return $this;
        }

        foreach ($this->children as $child) {
            $match = $child->find($id);
            if (null !== $match) {
                return $match;
            }
        }

        return null;
    }

    public function frameOf(string $id): ?Rect
    {
        return $this->find($id)?->frame;
    }

    /**
     * Direct children whose frame is not fully inside this node's frame.
     *
     * Overflow is derived rather than stored, so it stays true for every
     * container instead of being a flag one of them remembers to set. An
     * anchored child pushed out by an offset, a fixed child larger than its
     * slot, and a track that outgrew its band all surface the same way.
     *
     * @return list<self>
     */
    public function overflowingChildren(float $tolerance = 1e-9): array
    {
        return array_values(array_filter(
            $this->children,
            fn (self $child): bool => !$this->frame->containsRect($child->frame, $tolerance),
        ));
    }

    /**
     * True when any direct child sticks out of this node's frame.
     */
    public function overflows(float $tolerance = 1e-9): bool
    {
        return [] !== $this->overflowingChildren($tolerance);
    }
}
