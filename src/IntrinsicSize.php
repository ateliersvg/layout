<?php

declare(strict_types=1);

namespace Atelier\Layout;

use Atelier\Layout\Geometry\Size;

final readonly class IntrinsicSize
{
    public function __construct(
        public Size $size,
        public ?float $firstBaseline = null,
        public ?float $lastBaseline = null,
    ) {
    }
}
