<?php

declare(strict_types=1);

namespace Atelier\Layout\Connection;

enum ConnectionLabelPlacement
{
    case Above;
    case Below;
    case Centered;
    case EndpointStart;
    case EndpointEnd;
}
