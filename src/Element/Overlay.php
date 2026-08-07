<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\Anchor;
use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\IntrinsicSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\LayoutNodeInterface;
use Atelier\Layout\Result\PlacedNode;
use Atelier\Layout\Utility\Snap;
use Atelier\Layout\Value\InsetSpec;

final readonly class Overlay implements LayoutNodeInterface
{
    /**
     * @param list<array{node: LayoutNodeInterface, subject: Anchor, target: Anchor, offsetX: float, offsetY: float}> $children
     */
    public function __construct(
        private string $id,
        private array $children = [],
        private ?InsetSpec $padding = null,
    ) {
    }

    public static function of(string $id): OverlayBuilder
    {
        return OverlayBuilder::of($id);
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
            $measure = $child['node']->measure($context, BoxConstraints::unconstrained());
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
        $solvedChildren = [];

        foreach ($this->children as $child) {
            $node = $child['node'];
            $measure = $node->measure($context, new BoxConstraints(maxWidth: $content->width, maxHeight: $content->height));
            $childRect = Snap::rect(
                new Rect($content->x, $content->y, min($content->width, $measure->size->width), min($content->height, $measure->size->height)),
                $child['subject'],
                $content,
                $child['target'],
                $child['offsetX'],
                $child['offsetY'],
            );

            $solvedChildren[] = $node->solve($context, $childRect);
        }

        return new PlacedNode($this->id, $rect, new IntrinsicSize($rect->size()), $solvedChildren);
    }

    private function paddingSpec(): InsetSpec
    {
        return $this->padding ?? InsetSpec::zero();
    }
}
