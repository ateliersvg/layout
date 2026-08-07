<?php

declare(strict_types=1);

namespace Atelier\Layout\Fit;

enum FitMode
{
    case Contain;
    case Cover;
    case Fill;
    case None;
    case ScaleDown;
}
