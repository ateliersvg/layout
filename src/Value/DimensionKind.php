<?php

declare(strict_types=1);

namespace Atelier\Layout\Value;

enum DimensionKind
{
    case Fixed;
    case Auto;
    case Stretch;
    case MinContent;
    case MaxContent;
}
