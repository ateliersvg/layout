<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Grid\GridItem;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\LayoutNodeInterface;

/**
 * Fluent builder for a {@see Grid}.
 *
 * Children carry their span, so add() keeps the verb but takes optional span and
 * per-cell alignment. Entry points columns()/tracks() are unchanged.
 */
final class GridBuilder extends ContainerBuilder
{
    /** @var list<GridItem> */
    private array $items = [];
    private float $gapX = 0.0;
    private float $gapY = 0.0;
    private Alignment $alignX = Alignment::Stretch;
    private Alignment $alignY = Alignment::Stretch;

    /**
     * @param list<TrackSize>|null $columnTracks
     * @param list<TrackSize>|null $rowTracks
     */
    private function __construct(
        string $id,
        private readonly int $columns,
        private readonly ?array $columnTracks = null,
        private ?array $rowTracks = null,
    ) {
        parent::__construct($id);
    }

    public static function columns(string $id, int $columns): self
    {
        return new self($id, $columns);
    }

    /**
     * @param list<TrackSize>      $columns
     * @param list<TrackSize>|null $rows
     */
    public static function tracks(string $id, array $columns, ?array $rows = null): self
    {
        return new self($id, \count($columns), array_values($columns), null !== $rows ? array_values($rows) : null);
    }

    public function gap(float $x, ?float $y = null): static
    {
        $this->gapX = $x;
        $this->gapY = $y ?? $x;

        return $this;
    }

    public function align(Alignment $x, Alignment $y): static
    {
        $this->alignX = $x;
        $this->alignY = $y;

        return $this;
    }

    /**
     * @param non-empty-list<TrackSize> $rows
     */
    public function rows(array $rows): static
    {
        $this->rowTracks = array_values($rows);

        return $this;
    }

    public function add(LayoutNodeInterface $child, int $columnSpan = 1, int $rowSpan = 1, ?Alignment $alignX = null, ?Alignment $alignY = null): static
    {
        $columnCount = null !== $this->columnTracks ? \count($this->columnTracks) : $this->columns;
        if ($columnSpan > $columnCount) {
            throw new InvalidArgumentException('Grid item column span cannot exceed the grid column count.');
        }

        $this->items[] = new GridItem($child instanceof ContainerBuilder ? $child->build() : $child, $columnSpan, $rowSpan, $alignX, $alignY);

        return $this;
    }

    public function build(): Grid
    {
        return new Grid($this->id, $this->columns, $this->items, $this->gapX, $this->gapY, $this->alignX, $this->alignY, $this->padding, $this->columnTracks, $this->rowTracks);
    }
}
