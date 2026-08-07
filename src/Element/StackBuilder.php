<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Axis;
use Atelier\Layout\Distribution;

/**
 * Fluent builder for a {@see Stack}.
 *
 * Names disambiguated from the current API (flexbox vocabulary): the cross-axis
 * knob is alignItems() (the main axis is owned by distribute()), and the entry
 * points are row()/column() rather than horizontal()/vertical().
 */
final class StackBuilder extends NodeListBuilder
{
    private bool $baselineAlignment = false;

    private float $gap = 0.0;
    private Alignment $crossAlignment = Alignment::Start;
    private Distribution $distribution = Distribution::Start;

    private function __construct(string $id, private readonly Axis $axis)
    {
        parent::__construct($id);
    }

    public static function row(string $id): self
    {
        return new self($id, Axis::Horizontal);
    }

    public static function column(string $id): self
    {
        return new self($id, Axis::Vertical);
    }

    public function gap(float $gap): static
    {
        $this->gap = $gap;

        return $this;
    }

    /** Cross-axis alignment; the main axis is controlled by distribute(). */
    public function alignItems(Alignment $alignment): static
    {
        $this->crossAlignment = $alignment;

        return $this;
    }

    public function distribute(Distribution $distribution): static
    {
        $this->distribution = $distribution;

        return $this;
    }

    /**
     * Align children on a shared text baseline instead of on their box edges.
     *
     * Only meaningful on a horizontal stack, and only for children that report
     * a baseline; anything else keeps its top edge. Setting this makes the
     * cross-axis alignment moot, so it also disables stretching.
     */
    public function alignToBaseline(bool $baselineAlignment = true): static
    {
        $this->baselineAlignment = $baselineAlignment;

        return $this;
    }

    public function build(): Stack
    {
        return new Stack($this->id, $this->axis, $this->children, $this->gap, $this->crossAlignment, $this->distribution, $this->padding, $this->baselineAlignment);
    }
}
