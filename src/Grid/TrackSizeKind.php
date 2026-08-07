<?php

declare(strict_types=1);

namespace Atelier\Layout\Grid;

enum TrackSizeKind
{
    case Fixed;
    case Fraction;
    case Auto;
}
