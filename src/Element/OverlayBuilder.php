<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\Anchor;
use Atelier\Layout\LayoutNodeInterface;

/**
 * Fluent builder for an {@see Overlay}.
 *
 * Children carry their anchoring, so add() keeps the verb but takes the subject/
 * target anchors and offsets (defaulting to centered). This is the one container
 * whose children are not a plain node list, hence its own storage.
 */
final class OverlayBuilder extends ContainerBuilder
{
    /** @var list<array{node: LayoutNodeInterface, subject: Anchor, target: Anchor, offsetX: float, offsetY: float}> */
    private array $children = [];

    public static function of(string $id): self
    {
        return new self($id);
    }

    public function add(
        LayoutNodeInterface $child,
        Anchor $subject = Anchor::Center,
        Anchor $target = Anchor::Center,
        float $offsetX = 0.0,
        float $offsetY = 0.0,
    ): static {
        $this->children[] = [
            'node' => $child instanceof ContainerBuilder ? $child->build() : $child,
            'subject' => $subject,
            'target' => $target,
            'offsetX' => $offsetX,
            'offsetY' => $offsetY,
        ];

        return $this;
    }

    public function build(): Overlay
    {
        return new Overlay($this->id, $this->children, $this->padding);
    }
}
