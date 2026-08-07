<?php

declare(strict_types=1);

namespace Atelier\Layout\Connection;

enum PortSide
{
    case Top;
    case Right;
    case Bottom;
    case Left;

    public function isHorizontal(): bool
    {
        return self::Left === $this || self::Right === $this;
    }
}
