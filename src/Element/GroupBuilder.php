<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\Alignment;

/**
 * Fluent builder for a {@see Group}. Entry point of() rather than centered()
 * -- the centered default is expressed by align()'s defaults, not the factory.
 */
final class GroupBuilder extends NodeListBuilder
{
    private Alignment $alignX = Alignment::Center;
    private Alignment $alignY = Alignment::Center;

    public static function of(string $id): self
    {
        return new self($id);
    }

    public function align(Alignment $x, Alignment $y): static
    {
        $this->alignX = $x;
        $this->alignY = $y;

        return $this;
    }

    public function build(): Group
    {
        return new Group($this->id, $this->children, $this->alignX, $this->alignY, $this->padding);
    }
}
