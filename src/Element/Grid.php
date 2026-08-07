<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\Grid\GridItem;
use Atelier\Layout\Grid\GridSlot;
use Atelier\Layout\Grid\PlacedGrid;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\Grid\TrackSizeKind;
use Atelier\Layout\IntrinsicSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\LayoutNodeInterface;
use Atelier\Layout\Result\PlacedNode;
use Atelier\Layout\Result\PlacedTree;
use Atelier\Layout\Value\InsetSpec;

final readonly class Grid implements LayoutNodeInterface
{
    /**
     * @param list<GridItem>       $items
     * @param list<TrackSize>|null $columnTracks
     * @param list<TrackSize>|null $rowTracks
     */
    public function __construct(
        private string $id,
        private int $columns,
        private array $items = [],
        private float $gapX = 0.0,
        private float $gapY = 0.0,
        private Alignment $alignX = Alignment::Stretch,
        private Alignment $alignY = Alignment::Stretch,
        private ?InsetSpec $padding = null,
        private ?array $columnTracks = null,
        private ?array $rowTracks = null,
    ) {
    }

    public static function columns(string $id, int $columns): GridBuilder
    {
        return GridBuilder::columns($id, $columns);
    }

    /**
     * @param non-empty-list<TrackSize> $columns
     * @param list<TrackSize>|null      $rows
     */
    public static function tracks(string $id, array $columns, ?array $rows = null): GridBuilder
    {
        return GridBuilder::tracks($id, $columns, $rows);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function measure(LayoutContext $context, BoxConstraints $constraints): IntrinsicSize
    {
        $available = new Size(
            $constraints->maxWidth < PHP_FLOAT_MAX ? $constraints->maxWidth : 0.0,
            $constraints->maxHeight < PHP_FLOAT_MAX ? $constraints->maxHeight : 0.0,
        );
        $insets = $this->paddingSpec()->resolve($available);
        $placements = $this->placements();
        $columnTracks = $this->columnTracks();
        $rowTracks = $this->rowTracks($placements);
        $availableWidth = $constraints->maxWidth < PHP_FLOAT_MAX ? max(0.0, $constraints->maxWidth - $insets->horizontal() - max(0, \count($columnTracks) - 1) * $this->gapX) : $this->preferredTrackSpace($context, $placements, true);
        $availableHeight = $constraints->maxHeight < PHP_FLOAT_MAX ? max(0.0, $constraints->maxHeight - $insets->vertical() - max(0, \count($rowTracks) - 1) * $this->gapY) : $this->preferredTrackSpace($context, $placements, false);
        $trackWidths = $this->resolveTrackSizes($context, $availableWidth, $columnTracks, $placements, true);
        $trackHeights = $this->resolveTrackSizes($context, $availableHeight, $rowTracks, $placements, false);
        $size = new Size(
            $insets->horizontal() + array_sum($trackWidths) + max(0, \count($columnTracks) - 1) * $this->gapX,
            $insets->vertical() + array_sum($trackHeights) + max(0, \count($rowTracks) - 1) * $this->gapY,
        );

        return new IntrinsicSize($constraints->constrain($size));
    }

    public function solve(LayoutContext $context, Rect $rect): PlacedNode
    {
        return $this->layout($context, $rect)->result->root;
    }

    public function layout(LayoutContext $context, Rect $rect): PlacedGrid
    {
        $placements = $this->placements();
        $columnTracks = $this->columnTracks();
        $rowTracks = $this->rowTracks($placements);
        $content = $rect->inset($this->paddingSpec()->resolve($rect->size()));
        $trackWidths = $this->resolveTrackSizes($context, max(0.0, $content->width - max(0, \count($columnTracks) - 1) * $this->gapX), $columnTracks, $placements, true);
        $trackHeights = $this->resolveTrackSizes($context, max(0.0, $content->height - max(0, \count($rowTracks) - 1) * $this->gapY), $rowTracks, $placements, false);
        $columnFrames = [];
        $x = $content->x;
        foreach ($trackWidths as $width) {
            $columnFrames[] = new Rect($x, $content->y, $width, $content->height);
            $x += $width + $this->gapX;
        }
        $rowFrames = [];
        $y = $content->y;
        foreach ($trackHeights as $height) {
            $rowFrames[] = new Rect($content->x, $y, $content->width, $height);
            $y += $height + $this->gapY;
        }
        $slotFrames = [];
        foreach ($rowFrames as $rowIndex => $rowFrame) {
            foreach ($columnFrames as $columnIndex => $columnFrame) {
                $slotFrames[$rowIndex][$columnIndex] = new Rect($columnFrame->x, $rowFrame->y, $columnFrame->width, $rowFrame->height);
            }
        }

        $solvedChildren = [];
        $slots = [];
        foreach ($placements as $placement) {
            $item = $placement['item'];
            $column = $placement['column'];
            $row = $placement['row'];
            $slot = $this->slotFrame($columnFrames, $rowFrames, $column, $row, $item->columnSpan, $item->rowSpan);
            $alignX = $item->alignX ?? $this->alignX;
            $alignY = $item->alignY ?? $this->alignY;
            $measure = $item->node->measure($context, new BoxConstraints(maxWidth: $slot->width, maxHeight: $slot->height));
            $childWidth = Alignment::Stretch === $alignX ? $slot->width : min($slot->width, $measure->size->width);
            $childHeight = Alignment::Stretch === $alignY ? $slot->height : min($slot->height, $measure->size->height);
            $x = $this->alignOffset($slot->x, $slot->width, $childWidth, $alignX);
            $y = $this->alignOffset($slot->y, $slot->height, $childHeight, $alignY);

            $solvedChildren[] = $item->node->solve($context, new Rect($x, $y, $childWidth, $childHeight));
            $slots[] = new GridSlot($item->node->id(), $column, $row, $item->columnSpan, $item->rowSpan, $slot);
        }

        return new PlacedGrid(new PlacedTree(new PlacedNode($this->id, $rect, new IntrinsicSize($rect->size()), $solvedChildren)), $slots, $columnFrames, $rowFrames, array_values(array_map('array_values', $slotFrames)));
    }

    /**
     * @param list<Rect> $columnFrames
     * @param list<Rect> $rowFrames
     */
    private function slotFrame(array $columnFrames, array $rowFrames, int $column, int $row, int $columnSpan, int $rowSpan): Rect
    {
        $startColumn = $columnFrames[$column];
        $startRow = $rowFrames[$row];
        $width = array_sum(\array_map(static fn (Rect $frame): float => $frame->width, \array_slice($columnFrames, $column, $columnSpan))) + max(0, $columnSpan - 1) * $this->gapX;
        $height = array_sum(\array_map(static fn (Rect $frame): float => $frame->height, \array_slice($rowFrames, $row, $rowSpan))) + max(0, $rowSpan - 1) * $this->gapY;

        return new Rect($startColumn->x, $startRow->y, $width, $height);
    }

    private function paddingSpec(): InsetSpec
    {
        return $this->padding ?? InsetSpec::zero();
    }

    /**
     * @return non-empty-list<TrackSize>
     */
    private function columnTracks(): array
    {
        if (null !== $this->columnTracks && [] !== $this->columnTracks) {
            return $this->columnTracks;
        }

        return array_fill(0, max(1, $this->columns), TrackSize::fr());
    }

    /**
     * @param list<array{item: GridItem, column: int, row: int}> $placements
     *
     * @return non-empty-list<TrackSize>
     */
    private function rowTracks(array $placements): array
    {
        $rowCount = 1;
        foreach ($placements as $placement) {
            $rowCount = max($rowCount, $placement['row'] + $placement['item']->rowSpan);
        }

        $tracks = $this->rowTracks ?? [];
        while (\count($tracks) < $rowCount) {
            $tracks[] = TrackSize::fr();
        }

        return $tracks;
    }

    /**
     * @return list<array{item: GridItem, column: int, row: int}>
     */
    private function placements(): array
    {
        $columns = \count($this->columnTracks());
        $occupied = [];
        $placements = [];
        $cursorRow = 0;
        $cursorColumn = 0;

        foreach ($this->items as $item) {
            $span = min($item->columnSpan, $columns);
            while (!$this->fits($occupied, $cursorColumn, $cursorRow, $span, $item->rowSpan, $columns)) {
                ++$cursorColumn;
                if ($cursorColumn >= $columns) {
                    $cursorColumn = 0;
                    ++$cursorRow;
                }
            }

            $placements[] = ['item' => $item, 'column' => $cursorColumn, 'row' => $cursorRow];
            for ($row = $cursorRow; $row < $cursorRow + $item->rowSpan; ++$row) {
                for ($column = $cursorColumn; $column < $cursorColumn + $span; ++$column) {
                    $occupied[$row][$column] = true;
                }
            }
        }

        return $placements;
    }

    /**
     * @param array<int, array<int, bool>> $occupied
     */
    private function fits(array $occupied, int $column, int $row, int $columnSpan, int $rowSpan, int $columns): bool
    {
        if ($column + $columnSpan > $columns) {
            return false;
        }

        for ($y = $row; $y < $row + $rowSpan; ++$y) {
            for ($x = $column; $x < $column + $columnSpan; ++$x) {
                if (($occupied[$y][$x] ?? false) === true) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param list<array{item: GridItem, column: int, row: int}> $placements
     */
    private function preferredTrackSpace(LayoutContext $context, array $placements, bool $horizontal): float
    {
        $sizes = $this->preferredTrackSizes($context, $placements, $horizontal);

        return array_sum($sizes);
    }

    /**
     * @param list<array{item: GridItem, column: int, row: int}> $placements
     *
     * @return list<float>
     */
    private function preferredTrackSizes(LayoutContext $context, array $placements, bool $horizontal): array
    {
        $count = $horizontal ? \count($this->columnTracks()) : \count($this->rowTracks($placements));
        $sizes = array_values(array_fill(0, $count, 0.0));

        foreach ($placements as $placement) {
            $item = $placement['item'];
            $span = $horizontal ? min($item->columnSpan, $count) : $item->rowSpan;
            if (1 !== $span) {
                continue;
            }

            $measure = $item->node->measure($context, BoxConstraints::unconstrained());
            $index = $horizontal ? $placement['column'] : $placement['row'];
            $sizes[$index] = max($sizes[$index], $horizontal ? $measure->size->width : $measure->size->height);
        }

        return array_values($sizes);
    }

    /**
     * @param non-empty-list<TrackSize>                          $tracks
     * @param list<array{item: GridItem, column: int, row: int}> $placements
     *
     * @return list<float>
     */
    private function resolveTrackSizes(LayoutContext $context, float $available, array $tracks, array $placements, bool $horizontal): array
    {
        $fixed = 0.0;
        $fraction = 0.0;
        $widths = array_values(array_fill(0, \count($tracks), 0.0));
        $preferred = $this->preferredTrackSizes($context, $placements, $horizontal);

        foreach ($tracks as $index => $track) {
            if (TrackSizeKind::Fixed === $track->kind) {
                $widths[$index] = $track->value;
                $fixed += $track->value;
                continue;
            }
            if (TrackSizeKind::Fraction === $track->kind) {
                $fraction += $track->value;
                continue;
            }

            $widths[$index] = $preferred[$index] ?? 0.0;
            $fixed += $widths[$index];
        }

        $remaining = max(0.0, $available - $fixed);
        foreach ($tracks as $index => $track) {
            if (TrackSizeKind::Fraction !== $track->kind) {
                continue;
            }
            $widths[$index] = $fraction > 0.0 ? $remaining * $track->value / $fraction : 0.0;
        }

        return array_values($widths);
    }

    private function alignOffset(float $origin, float $trackSize, float $childSize, Alignment $alignment): float
    {
        $free = max(0.0, $trackSize - $childSize);

        return match ($alignment) {
            Alignment::Start, Alignment::Stretch => $origin,
            Alignment::Center => $origin + $free / 2.0,
            Alignment::End => $origin + $free,
        };
    }
}
