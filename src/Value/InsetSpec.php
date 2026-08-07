<?php

declare(strict_types=1);

namespace Atelier\Layout\Value;

use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Size;

final readonly class InsetSpec
{
    public function __construct(
        public Length $top,
        public Length $right,
        public Length $bottom,
        public Length $left,
    ) {
    }

    public static function zero(): self
    {
        return self::px(0.0);
    }

    public static function px(float $value): self
    {
        $length = Length::px($value);

        return new self($length, $length, $length, $length);
    }

    public static function percent(float $value): self
    {
        $length = Length::percent($value);

        return new self($length, $length, $length, $length);
    }

    public function resolve(Size $size): Insets
    {
        return new Insets(
            top: $this->top->place($size->height),
            right: $this->right->place($size->width),
            bottom: $this->bottom->place($size->height),
            left: $this->left->place($size->width),
        );
    }
}
