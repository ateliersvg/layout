<?php

declare(strict_types=1);

namespace Atelier\Layout\Legend;

use Atelier\Layout\Alignment;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Value\InsetSpec;

final readonly class LegendBlock
{
    /**
     * @param list<array{id: string, labelWidth: float, labelHeight: float}> $entries
     */
    private function __construct(
        private string $id,
        private bool $vertical = true,
        private array $entries = [],
        private float $gap = 0.0,
        private float $labelGap = 0.0,
        private float $swatchWidth = 0.0,
        private float $swatchHeight = 0.0,
        private ?InsetSpec $padding = null,
        private Alignment $alignX = Alignment::Start,
        private Alignment $alignY = Alignment::Start,
    ) {
        if ($gap < 0.0 || $labelGap < 0.0 || $swatchWidth < 0.0 || $swatchHeight < 0.0) {
            throw new InvalidArgumentException('Legend layout spacing and swatch size must not be negative.');
        }
    }

    public static function vertical(string $id): self
    {
        return new self($id, true);
    }

    public static function horizontal(string $id): self
    {
        return new self($id, false);
    }

    public function gap(float $gap): self
    {
        return new self($this->id, $this->vertical, $this->entries, $gap, $this->labelGap, $this->swatchWidth, $this->swatchHeight, $this->padding, $this->alignX, $this->alignY);
    }

    public function labelGap(float $gap): self
    {
        return new self($this->id, $this->vertical, $this->entries, $this->gap, $gap, $this->swatchWidth, $this->swatchHeight, $this->padding, $this->alignX, $this->alignY);
    }

    public function swatchSize(float $width, float $height): self
    {
        return new self($this->id, $this->vertical, $this->entries, $this->gap, $this->labelGap, $width, $height, $this->padding, $this->alignX, $this->alignY);
    }

    public function padding(InsetSpec $padding): self
    {
        return new self($this->id, $this->vertical, $this->entries, $this->gap, $this->labelGap, $this->swatchWidth, $this->swatchHeight, $padding, $this->alignX, $this->alignY);
    }

    public function align(Alignment $x, Alignment $y): self
    {
        return new self($this->id, $this->vertical, $this->entries, $this->gap, $this->labelGap, $this->swatchWidth, $this->swatchHeight, $this->padding, $x, $y);
    }

    public function add(string $id, float $labelWidth, float $labelHeight): self
    {
        if ($labelWidth < 0.0 || $labelHeight < 0.0) {
            throw new InvalidArgumentException('Legend label size must not be negative.');
        }

        return new self($this->id, $this->vertical, [...$this->entries, ['id' => $id, 'labelWidth' => $labelWidth, 'labelHeight' => $labelHeight]], $this->gap, $this->labelGap, $this->swatchWidth, $this->swatchHeight, $this->padding, $this->alignX, $this->alignY);
    }

    public function place(Rect $available): PlacedLegend
    {
        $content = $available->inset($this->paddingSpec()->resolve($available->size()));

        if ([] === $this->entries) {
            return new PlacedLegend($this->id, $content, [], false, false);
        }

        $cursor = $this->vertical ? $content->y : $content->x;
        $cursorStart = $cursor;
        $maxCross = 0.0;
        $usedMain = 0.0;
        $resolvedEntries = [];

        foreach ($this->entries as $entry) {
            $entryWidth = $this->swatchWidth + $this->labelGap + $entry['labelWidth'];
            $entryHeight = max($this->swatchHeight, $entry['labelHeight']);

            if ($this->vertical) {
                $frame = new Rect($content->x, $cursor, $entryWidth, $entryHeight);
                $cursor += $entryHeight + $this->gap;
                $usedMain = $cursor - $cursorStart - $this->gap;
                $maxCross = max($maxCross, $entryWidth);
            } else {
                $frame = new Rect($cursor, $content->y, $entryWidth, $entryHeight);
                $cursor += $entryWidth + $this->gap;
                $usedMain = $cursor - $cursorStart - $this->gap;
                $maxCross = max($maxCross, $entryHeight);
            }

            $swatchFrame = $this->vertical
                ? new Rect($frame->x, $frame->y + ($entryHeight - $this->swatchHeight) / 2.0, $this->swatchWidth, $this->swatchHeight)
                : new Rect($frame->x, $frame->y + ($entryHeight - $this->swatchHeight) / 2.0, $this->swatchWidth, $this->swatchHeight);
            $labelFrame = new Rect($swatchFrame->right() + $this->labelGap, $frame->y + ($entryHeight - $entry['labelHeight']) / 2.0, $entry['labelWidth'], $entry['labelHeight']);

            $resolvedEntries[] = new PlacedLegendEntry($entry['id'], $frame, $swatchFrame, $labelFrame);
        }

        $intrinsic = $this->vertical
            ? new Rect($content->x, $content->y, $maxCross, $usedMain)
            : new Rect($content->x, $content->y, $usedMain, $maxCross);
        $offsetX = $this->alignOffset($content->x, $content->width, $intrinsic->width, $this->alignX);
        $offsetY = $this->alignOffset($content->y, $content->height, $intrinsic->height, $this->alignY);

        foreach ($resolvedEntries as $index => $entry) {
            $resolvedEntries[$index] = new PlacedLegendEntry(
                $entry->id,
                new Rect($entry->frame->x + $offsetX - $content->x, $entry->frame->y + $offsetY - $content->y, $entry->frame->width, $entry->frame->height),
                new Rect($entry->swatchFrame->x + $offsetX - $content->x, $entry->swatchFrame->y + $offsetY - $content->y, $entry->swatchFrame->width, $entry->swatchFrame->height),
                new Rect($entry->labelFrame->x + $offsetX - $content->x, $entry->labelFrame->y + $offsetY - $content->y, $entry->labelFrame->width, $entry->labelFrame->height),
            );
        }

        $frame = new Rect($offsetX, $offsetY, $intrinsic->width, $intrinsic->height);

        return new PlacedLegend(
            $this->id,
            $frame,
            $resolvedEntries,
            $intrinsic->width > $content->width,
            $intrinsic->height > $content->height,
        );
    }

    private function paddingSpec(): InsetSpec
    {
        return $this->padding ?? InsetSpec::zero();
    }

    private function alignOffset(float $origin, float $available, float $used, Alignment $alignment): float
    {
        $free = max(0.0, $available - $used);

        return match ($alignment) {
            Alignment::Start, Alignment::Stretch => $origin,
            Alignment::Center => $origin + $free / 2.0,
            Alignment::End => $origin + $free,
        };
    }
}
