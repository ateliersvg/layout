<?php

declare(strict_types=1);

namespace Atelier\Layout\Element;

use Atelier\Layout\Alignment;
use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\IntrinsicSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\LayoutNodeInterface;
use Atelier\Layout\Result\PlacedNode;
use Atelier\Layout\Text\FontWeight;
use Atelier\Layout\Text\TextLayout;
use Atelier\Layout\Text\TextLineLayout;

final readonly class TextBlock implements LayoutNodeInterface
{
    public function __construct(
        private string $id,
        private string $text,
        private float $fontSize = 14.0,
        private float $lineHeight = 1.2,
        private bool $breakWords = false,
        private FontWeight $fontWeight = FontWeight::Normal,
        private Alignment $alignX = Alignment::Start,
        private Alignment $alignY = Alignment::Start,
        private ?int $maxLines = null,
    ) {
        if (null !== $maxLines && $maxLines < 1) {
            throw new InvalidArgumentException(sprintf('maxLines must be at least 1, got %d.', $maxLines));
        }
    }

    public static function of(string $id, string $text, float $fontSize = 14.0): self
    {
        return new self($id, $text, $fontSize);
    }

    public function lineHeight(float $lineHeight): self
    {
        return new self($this->id, $this->text, $this->fontSize, $lineHeight, $this->breakWords, $this->fontWeight, $this->alignX, $this->alignY, $this->maxLines);
    }

    public function breakWords(bool $breakWords = true): self
    {
        return new self($this->id, $this->text, $this->fontSize, $this->lineHeight, $breakWords, $this->fontWeight, $this->alignX, $this->alignY, $this->maxLines);
    }

    public function weight(FontWeight $weight): self
    {
        return new self($this->id, $this->text, $this->fontSize, $this->lineHeight, $this->breakWords, $weight, $this->alignX, $this->alignY, $this->maxLines);
    }

    /**
     * Cap the number of rendered lines. Extra lines are dropped and the layout
     * reports itself as truncated; adding an ellipsis is a rendering decision
     * and stays with the consumer.
     */
    public function maxLines(?int $maxLines): self
    {
        return new self($this->id, $this->text, $this->fontSize, $this->lineHeight, $this->breakWords, $this->fontWeight, $this->alignX, $this->alignY, $maxLines);
    }

    public function align(Alignment $x, Alignment $y): self
    {
        return new self($this->id, $this->text, $this->fontSize, $this->lineHeight, $this->breakWords, $this->fontWeight, $x, $y, $this->maxLines);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function measure(LayoutContext $context, BoxConstraints $constraints): IntrinsicSize
    {
        if ($constraints->maxWidth < PHP_FLOAT_MAX) {
            $block = $context->textMeasurer->wrap($this->text, $constraints->maxWidth, $this->fontSize, $this->lineHeight, $this->breakWords, $this->fontWeight);
            $height = $this->cappedHeight($block->height, \count($block->lines));

            return new IntrinsicSize(
                $constraints->constrain(new Size($block->width, $height)),
                $block->firstBaseline,
                $height < $block->height ? null : $block->lastBaseline,
            );
        }

        $line = $context->textMeasurer->measureLine($this->text, $this->fontSize, $this->fontWeight);

        return new IntrinsicSize(
            $constraints->constrain(new Size($line->width, $line->height)),
            $line->ascent,
            $line->ascent,
        );
    }

    public function solve(LayoutContext $context, Rect $rect): PlacedNode
    {
        $measure = $this->measure($context, new BoxConstraints(maxWidth: $rect->width, maxHeight: $rect->height));

        return new PlacedNode($this->id, $rect, $measure);
    }

    public function layout(LayoutContext $context, Rect $rect): TextLayout
    {
        $block = $context->textMeasurer->wrap($this->text, $rect->width, $this->fontSize, $this->lineHeight, $this->breakWords, $this->fontWeight);
        $lineBoxHeight = $this->fontSize * $this->lineHeight;

        $visible = null !== $this->maxLines ? \array_slice($block->lines, 0, $this->maxLines) : $block->lines;
        $truncated = \count($visible) < \count($block->lines);

        // Vertical alignment must use the height actually rendered. Aligning on
        // the full block height would push a truncated End-aligned run out of
        // the rect by exactly the height of the dropped lines.
        $usedHeight = \count($visible) * $lineBoxHeight;
        $y = $this->alignOffset($rect->y, $rect->height, $usedHeight, $this->alignY);

        $lines = [];
        $overflowX = false;

        foreach ($visible as $index => $line) {
            $metrics = $context->textMeasurer->measureLine($line, $this->fontSize, $this->fontWeight);
            $overflowX = $overflowX || $metrics->width > $rect->width;
            $lineX = $this->alignOffset($rect->x, $rect->width, min($metrics->width, $rect->width), $this->alignX);
            $lineY = $y + $index * $lineBoxHeight;
            $lines[] = new TextLineLayout(
                $line,
                new Rect($lineX, $lineY, min($metrics->width, $rect->width), $lineBoxHeight),
                $lineY + $metrics->ascent,
            );
        }

        return new TextLayout(
            $rect,
            $lines,
            $block->width,
            $usedHeight,
            $overflowX,
            $usedHeight > $rect->height,
            $truncated,
        );
    }

    /**
     * Height once the line cap is applied. Without a cap the measured height
     * passes through untouched.
     */
    private function cappedHeight(float $measured, int $lineCount): float
    {
        if (null === $this->maxLines || $lineCount <= $this->maxLines) {
            return $measured;
        }

        return $this->maxLines * $this->fontSize * $this->lineHeight;
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
