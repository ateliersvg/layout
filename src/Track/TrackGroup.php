<?php

declare(strict_types=1);

namespace Atelier\Layout\Track;

use Atelier\Layout\Axis;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Value\Dimension;
use Atelier\Layout\Value\DimensionKind;
use Atelier\Layout\Value\InsetSpec;

final readonly class TrackGroup
{
    /**
     * @param list<array{id: string, mainSize: Dimension, preferredMainSize: float|null}> $tracks
     */
    private function __construct(
        private string $id,
        private Axis $axis,
        private array $tracks = [],
        private float $gap = 0.0,
        private ?InsetSpec $padding = null,
        private float $headerSize = 0.0,
        private float $footerSize = 0.0,
        private ?Dimension $defaultMainSize = null,
    ) {
        if ($gap < 0.0) {
            throw new InvalidArgumentException('Track group gap must not be negative.');
        }
        if ($headerSize < 0.0 || $footerSize < 0.0) {
            throw new InvalidArgumentException('Track group header and footer sizes must not be negative.');
        }
    }

    public static function horizontal(string $id): self
    {
        return new self($id, Axis::Horizontal);
    }

    public static function vertical(string $id): self
    {
        return new self($id, Axis::Vertical);
    }

    public function gap(float $gap): self
    {
        return new self($this->id, $this->axis, $this->tracks, $gap, $this->padding, $this->headerSize, $this->footerSize, $this->defaultMainSize);
    }

    public function padding(InsetSpec $padding): self
    {
        return new self($this->id, $this->axis, $this->tracks, $this->gap, $padding, $this->headerSize, $this->footerSize, $this->defaultMainSize);
    }

    public function headerSize(float $size): self
    {
        return new self($this->id, $this->axis, $this->tracks, $this->gap, $this->padding, $size, $this->footerSize, $this->defaultMainSize);
    }

    public function footerSize(float $size): self
    {
        return new self($this->id, $this->axis, $this->tracks, $this->gap, $this->padding, $this->headerSize, $size, $this->defaultMainSize);
    }

    public function equalTracks(): self
    {
        return new self($this->id, $this->axis, $this->tracks, $this->gap, $this->padding, $this->headerSize, $this->footerSize, Dimension::stretch());
    }

    public function contentSizedTracks(): self
    {
        return new self($this->id, $this->axis, $this->tracks, $this->gap, $this->padding, $this->headerSize, $this->footerSize, Dimension::auto());
    }

    public function stretchedTracks(float $flex = 1.0): self
    {
        return new self($this->id, $this->axis, $this->tracks, $this->gap, $this->padding, $this->headerSize, $this->footerSize, Dimension::stretch($flex));
    }

    public function addTrack(string $id, ?Dimension $mainSize = null, ?float $preferredMainSize = null): self
    {
        $mainSize ??= $this->defaultMainSize ?? Dimension::stretch();

        return new self($this->id, $this->axis, [...$this->tracks, ['id' => $id, 'mainSize' => $mainSize, 'preferredMainSize' => $preferredMainSize]], $this->gap, $this->padding, $this->headerSize, $this->footerSize, $this->defaultMainSize);
    }

    public function place(Rect $canvas): PlacedTrackGroup
    {
        $content = $canvas->inset($this->paddingSpec()->resolve($canvas->size()));
        $crossSize = Axis::Horizontal === $this->axis ? $content->height : $content->width;
        $header = min($crossSize, $this->headerSize);
        $footer = min(max(0.0, $crossSize - $header), $this->footerSize);
        $body = max(0.0, $crossSize - $header - $footer);

        $placedMainSizes = [];
        $stretchFlex = 0.0;
        $occupied = 0.0;
        foreach ($this->tracks as $track) {
            $mainSize = $track['mainSize'];
            if (DimensionKind::Stretch === $mainSize->kind) {
                $stretchFlex += $mainSize->value;
                $placedMainSizes[] = null;
                continue;
            }

            $size = $this->mainSize($track);
            $placedMainSizes[] = $size;
            $occupied += $size;
        }

        $availableMain = Axis::Horizontal === $this->axis ? $content->width : $content->height;
        $remaining = max(0.0, $availableMain - $occupied - max(0, \count($this->tracks) - 1) * $this->gap);
        foreach ($placedMainSizes as $index => $size) {
            if (null !== $size) {
                continue;
            }

            $flex = $this->tracks[$index]['mainSize']->value;
            $placedMainSizes[$index] = $stretchFlex > 0.0 ? $remaining * $flex / $stretchFlex : 0.0;
        }

        $tracks = [];
        $cursor = Axis::Horizontal === $this->axis ? $content->x : $content->y;
        foreach ($this->tracks as $index => $track) {
            $mainSize = $placedMainSizes[$index] ?? 0.0;

            if (Axis::Horizontal === $this->axis) {
                $frame = new Rect($cursor, $content->y, $mainSize, $content->height);
                $headerFrame = new Rect($frame->x, $frame->y, $frame->width, $header);
                $bodyFrame = new Rect($frame->x, $frame->y + $header, $frame->width, $body);
                $footerFrame = new Rect($frame->x, $frame->bottom() - $footer, $frame->width, $footer);
            } else {
                $frame = new Rect($content->x, $cursor, $content->width, $mainSize);
                $headerFrame = new Rect($frame->x, $frame->y, $header, $frame->height);
                $bodyFrame = new Rect($frame->x + $header, $frame->y, $body, $frame->height);
                $footerFrame = new Rect($frame->right() - $footer, $frame->y, $footer, $frame->height);
            }

            $tracks[] = new PlacedTrack($track['id'], $frame, $headerFrame, $bodyFrame, $footerFrame);
            $cursor += $mainSize + $this->gap;
        }

        return new PlacedTrackGroup($this->id, $this->axis, $canvas, $tracks);
    }

    /**
     * @param array{id: string, mainSize: Dimension, preferredMainSize: float|null} $track
     */
    private function mainSize(array $track): float
    {
        $mainSize = $track['mainSize'];

        return match ($mainSize->kind) {
            DimensionKind::Fixed => $mainSize->value,
            DimensionKind::Auto, DimensionKind::MinContent, DimensionKind::MaxContent => $track['preferredMainSize'] ?? 0.0,
            DimensionKind::Stretch => 0.0,
        };
    }

    private function paddingSpec(): InsetSpec
    {
        return $this->padding ?? InsetSpec::zero();
    }
}
