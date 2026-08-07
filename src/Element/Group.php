<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\IntrinsicSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\LayoutNodeInterface;
use Atelier\Layout\Result\PlacedNode;
use Atelier\Layout\Value\InsetSpec;

final readonly class Group implements LayoutNodeInterface
{
    /**
     * @param list<LayoutNodeInterface> $children
     */
    public function __construct(
        private string $id,
        private array $children = [],
        private Alignment $alignX = Alignment::Center,
        private Alignment $alignY = Alignment::Center,
        private ?InsetSpec $padding = null,
    ) {
    }

    public static function of(string $id): GroupBuilder
    {
        return GroupBuilder::of($id);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function measure(LayoutContext $context, BoxConstraints $constraints): IntrinsicSize
    {
        $maxWidth = 0.0;
        $maxHeight = 0.0;
        foreach ($this->children as $child) {
            $measure = $child->measure($context, BoxConstraints::unconstrained());
            $maxWidth = max($maxWidth, $measure->size->width);
            $maxHeight = max($maxHeight, $measure->size->height);
        }

        $available = new Size(
            $constraints->maxWidth < PHP_FLOAT_MAX ? $constraints->maxWidth : 0.0,
            $constraints->maxHeight < PHP_FLOAT_MAX ? $constraints->maxHeight : 0.0,
        );
        $insets = $this->paddingSpec()->resolve($available);

        return new IntrinsicSize($constraints->constrain(new Size($maxWidth + $insets->horizontal(), $maxHeight + $insets->vertical())));
    }

    public function solve(LayoutContext $context, Rect $rect): PlacedNode
    {
        $content = $rect->inset($this->paddingSpec()->resolve($rect->size()));
        $children = [];

        foreach ($this->children as $child) {
            $measure = $child->measure($context, new BoxConstraints(maxWidth: $content->width, maxHeight: $content->height));
            $width = Alignment::Stretch === $this->alignX ? $content->width : min($content->width, $measure->size->width);
            $height = Alignment::Stretch === $this->alignY ? $content->height : min($content->height, $measure->size->height);
            $x = $this->alignOffset($content->x, $content->width, $width, $this->alignX);
            $y = $this->alignOffset($content->y, $content->height, $height, $this->alignY);

            $children[] = $child->solve($context, new Rect($x, $y, $width, $height));
        }

        return new PlacedNode($this->id, $rect, new IntrinsicSize($rect->size()), $children);
    }

    private function paddingSpec(): InsetSpec
    {
        return $this->padding ?? InsetSpec::zero();
    }

    private function alignOffset(float $origin, float $available, float $size, Alignment $alignment): float
    {
        $free = max(0.0, $available - $size);

        return match ($alignment) {
            Alignment::Start, Alignment::Stretch => $origin,
            Alignment::Center => $origin + $free / 2.0,
            Alignment::End => $origin + $free,
        };
    }
}
