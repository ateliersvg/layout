<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Axis;
use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Distribution;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\FlexibleLayoutNodeInterface;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\IntrinsicSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\LayoutNodeInterface;
use Atelier\Layout\Result\PlacedNode;
use Atelier\Layout\Value\InsetSpec;

final readonly class Stack implements LayoutNodeInterface
{
    /**
     * @param list<LayoutNodeInterface> $children
     */
    public function __construct(
        private string $id,
        private Axis $axis,
        private array $children = [],
        private float $gap = 0.0,
        private Alignment $alignment = Alignment::Start,
        private Distribution $distribution = Distribution::Start,
        private ?InsetSpec $padding = null,
        private bool $baselineAlignment = false,
    ) {
        if ($baselineAlignment && Axis::Horizontal !== $axis) {
            throw new InvalidArgumentException('Baseline alignment only applies to a horizontal stack.');
        }
    }

    public static function row(string $id): StackBuilder
    {
        return StackBuilder::row($id);
    }

    public static function column(string $id): StackBuilder
    {
        return StackBuilder::column($id);
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

        $main = 0.0;
        $cross = 0.0;
        foreach ($this->children as $child) {
            $measure = $child->measure($context, BoxConstraints::unconstrained());
            $main += $this->main($measure->size);
            $cross = max($cross, $this->cross($measure->size));
        }

        if ([] !== $this->children) {
            $main += $this->gap * (\count($this->children) - 1);
        }

        $size = Axis::Horizontal === $this->axis
            ? new Size($main + $insets->horizontal(), $cross + $insets->vertical())
            : new Size($cross + $insets->horizontal(), $main + $insets->vertical());

        return new IntrinsicSize($constraints->constrain($size));
    }

    public function solve(LayoutContext $context, Rect $rect): PlacedNode
    {
        $insets = $this->paddingSpec()->resolve($rect->size());
        $content = $rect->inset($insets);
        $measures = [];
        $baseMain = 0.0;
        $flexTotal = 0.0;

        foreach ($this->children as $child) {
            $constraints = Axis::Horizontal === $this->axis
                ? new BoxConstraints(maxHeight: $content->height)
                : new BoxConstraints(maxWidth: $content->width);
            $measure = $child->measure($context, $constraints);
            $measures[] = $measure;
            $baseMain += $this->main($measure->size);
            if ($child instanceof FlexibleLayoutNodeInterface) {
                $flexTotal += max(0.0, $child->flex());
            }
        }

        $count = \count($this->children);
        $baseMain += $count > 0 ? $this->gap * ($count - 1) : 0.0;
        $free = max(0.0, $this->main($content->size()) - $baseMain);
        [$leading, $extraGap] = $flexTotal > 0.0 ? [0.0, 0.0] : $this->distributionOffsets($free, $count);

        $commonBaseline = $this->baselineAlignment ? $this->commonBaseline($measures) : 0.0;

        $cursor = $this->mainOrigin($content) + $leading;
        $solvedChildren = [];
        foreach ($this->children as $index => $child) {
            $measure = $measures[$index];
            $mainSize = $this->main($measure->size);
            if ($flexTotal > 0.0 && $child instanceof FlexibleLayoutNodeInterface && $child->flex() > 0.0) {
                $mainSize += $free * $child->flex() / $flexTotal;
            }

            $crossSize = Alignment::Stretch === $this->alignment && !$this->baselineAlignment
                ? $this->cross($content->size())
                : $this->cross($measure->size);
            $crossOrigin = $this->baselineAlignment
                ? $this->baselineCrossOrigin($content, $measure, $commonBaseline)
                : $this->alignedCrossOrigin($content, $crossSize);
            $childRect = Axis::Horizontal === $this->axis
                ? new Rect($cursor, $crossOrigin, $mainSize, $crossSize)
                : new Rect($crossOrigin, $cursor, $crossSize, $mainSize);

            $solvedChildren[] = $child->solve($context, $childRect);
            $cursor += $mainSize + $this->gap + $extraGap;
        }

        return new PlacedNode($this->id, $rect, new IntrinsicSize($rect->size()), $solvedChildren);
    }

    private function main(Size $size): float
    {
        return Axis::Horizontal === $this->axis ? $size->width : $size->height;
    }

    private function paddingSpec(): InsetSpec
    {
        return $this->padding ?? InsetSpec::zero();
    }

    private function cross(Size $size): float
    {
        return Axis::Horizontal === $this->axis ? $size->height : $size->width;
    }

    private function mainOrigin(Rect $rect): float
    {
        return Axis::Horizontal === $this->axis ? $rect->x : $rect->y;
    }

    private function crossOrigin(Rect $rect): float
    {
        return Axis::Horizontal === $this->axis ? $rect->y : $rect->x;
    }

    /**
     * Distance from the top of a row to the line every child sits on.
     *
     * The deepest first baseline wins, so no child is pushed above the content
     * box. A child without a baseline (a plain frame among text blocks) keeps
     * its own top edge, which is what Start alignment would have given it.
     *
     * @param list<IntrinsicSize> $measures
     */
    private function commonBaseline(array $measures): float
    {
        $deepest = 0.0;
        foreach ($measures as $measure) {
            if (null !== $measure->firstBaseline) {
                $deepest = max($deepest, $measure->firstBaseline);
            }
        }

        return $deepest;
    }

    private function baselineCrossOrigin(Rect $content, IntrinsicSize $measure, float $commonBaseline): float
    {
        $crossOrigin = $this->crossOrigin($content);

        if (null === $measure->firstBaseline) {
            return $crossOrigin;
        }

        return $crossOrigin + $commonBaseline - $measure->firstBaseline;
    }

    private function alignedCrossOrigin(Rect $content, float $crossSize): float
    {
        $crossOrigin = $this->crossOrigin($content);
        $free = max(0.0, $this->cross($content->size()) - $crossSize);

        return match ($this->alignment) {
            Alignment::Start, Alignment::Stretch => $crossOrigin,
            Alignment::Center => $crossOrigin + $free / 2.0,
            Alignment::End => $crossOrigin + $free,
        };
    }

    /**
     * @return array{float, float} leading space and extra space added after each gap
     */
    private function distributionOffsets(float $free, int $count): array
    {
        if ($count <= 0) {
            return [0.0, 0.0];
        }

        return match ($this->distribution) {
            Distribution::Start => [0.0, 0.0],
            Distribution::Center => [$free / 2.0, 0.0],
            Distribution::End => [$free, 0.0],
            Distribution::SpaceBetween => $count > 1 ? [0.0, $free / ($count - 1)] : [$free / 2.0, 0.0],
            Distribution::SpaceAround => [$free / (2.0 * $count), $free / $count],
            Distribution::SpaceEvenly => [$free / ($count + 1), $free / ($count + 1)],
        };
    }
}
