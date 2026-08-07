<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\FlexibleLayoutNodeInterface;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\IntrinsicSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\LayoutNodeInterface;
use Atelier\Layout\Result\PlacedNode;

final readonly class Frame implements LayoutNodeInterface, FlexibleLayoutNodeInterface
{
    public function __construct(
        private string $id,
        private BoxConstraints $constraints,
        private float $flex = 0.0,
    ) {
    }

    public static function fixed(string $id, float $width, float $height): self
    {
        return new self($id, BoxConstraints::tight($width, $height));
    }

    public static function preferred(string $id, float $width, float $height, float $minWidth = 0.0, float $minHeight = 0.0): self
    {
        return new self($id, new BoxConstraints($minWidth, $minHeight, preferredWidth: $width, preferredHeight: $height));
    }

    public static function stretch(string $id, float $flex = 1.0, float $minWidth = 0.0, float $minHeight = 0.0): self
    {
        return new self($id, new BoxConstraints($minWidth, $minHeight), $flex);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function flex(): float
    {
        return $this->flex;
    }

    public function measure(LayoutContext $context, BoxConstraints $constraints): IntrinsicSize
    {
        $size = new Size(
            $this->constraints->preferredWidth ?? $this->constraints->minWidth,
            $this->constraints->preferredHeight ?? $this->constraints->minHeight,
        );

        return new IntrinsicSize($constraints->constrain($this->constraints->constrain($size)));
    }

    public function solve(LayoutContext $context, Rect $rect): PlacedNode
    {
        return new PlacedNode($this->id, $rect, new IntrinsicSize($rect->size()));
    }
}
