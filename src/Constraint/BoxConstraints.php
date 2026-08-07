<?php

declare(strict_types=1);

namespace Atelier\Layout\Constraint;

use Atelier\Layout\Exception\InvalidArgumentException;
use Atelier\Layout\Geometry\Size;

final readonly class BoxConstraints
{
    public function __construct(
        public float $minWidth = 0.0,
        public float $minHeight = 0.0,
        public float $maxWidth = PHP_FLOAT_MAX,
        public float $maxHeight = PHP_FLOAT_MAX,
        public ?float $preferredWidth = null,
        public ?float $preferredHeight = null,
    ) {
        if ($minWidth < 0.0 || $minHeight < 0.0 || $maxWidth < 0.0 || $maxHeight < 0.0) {
            throw new InvalidArgumentException('Frame constraint dimensions must not be negative.');
        }
        if ($minWidth > $maxWidth || $minHeight > $maxHeight) {
            throw new InvalidArgumentException('Frame minimum constraints must not exceed maximum constraints.');
        }
    }

    public static function unconstrained(): self
    {
        return new self();
    }

    public static function tight(float $width, float $height): self
    {
        return new self($width, $height, $width, $height, $width, $height);
    }

    public function constrain(Size $size): Size
    {
        return new Size(
            min($this->maxWidth, max($this->minWidth, $size->width)),
            min($this->maxHeight, max($this->minHeight, $size->height)),
        );
    }
}
