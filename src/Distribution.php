<?php

declare(strict_types=1);

namespace Atelier\Layout;

enum Distribution
{
    case Start;
    case Center;
    case End;
    case SpaceBetween;
    case SpaceAround;
    case SpaceEvenly;
}
