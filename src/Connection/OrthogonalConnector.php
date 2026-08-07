<?php

declare(strict_types=1);

namespace Atelier\Layout\Connection;

use Atelier\Layout\Anchor;
use Atelier\Layout\Geometry\Point;
use Atelier\Layout\Geometry\Rect;

final readonly class OrthogonalConnector
{
    public function connect(Rect $from, Rect $to): OrthogonalConnection
    {
        $fromCenter = $from->pointAt(Anchor::Center);
        $toCenter = $to->pointAt(Anchor::Center);

        if (abs($toCenter->x - $fromCenter->x) >= abs($toCenter->y - $fromCenter->y)) {
            return $this->connectPorts(
                Port::on($from, $toCenter->x >= $fromCenter->x ? PortSide::Right : PortSide::Left),
                Port::on($to, $toCenter->x >= $fromCenter->x ? PortSide::Left : PortSide::Right),
            );
        }

        return $this->connectPorts(
            Port::on($from, $toCenter->y >= $fromCenter->y ? PortSide::Bottom : PortSide::Top),
            Port::on($to, $toCenter->y >= $fromCenter->y ? PortSide::Top : PortSide::Bottom),
        );
    }

    public function connectPorts(Port $start, Port $end): OrthogonalConnection
    {
        $sx = $start->point->x;
        $sy = $start->point->y;
        $tx = $end->point->x;
        $ty = $end->point->y;

        if (abs($sx - $tx) < 0.01 || abs($sy - $ty) < 0.01) {
            $points = [$start->point, $end->point];

            return new OrthogonalConnection(
                $start,
                $end,
                $points,
                OrthogonalConnection::segmentsForPoints($points),
                new Point(($sx + $tx) / 2.0, ($sy + $ty) / 2.0),
                new Point($tx - $sx, $ty - $sy),
            );
        }

        if ($start->side->isHorizontal()) {
            $mid = ($sx + $tx) / 2.0;
            $points = [
                $start->point,
                new Point($mid, $sy),
                new Point($mid, $ty),
                $end->point,
            ];
            $labelPoint = new Point($mid, ($sy + $ty) / 2.0);
        } else {
            $mid = ($sy + $ty) / 2.0;
            $points = [
                $start->point,
                new Point($sx, $mid),
                new Point($tx, $mid),
                $end->point,
            ];
            $labelPoint = new Point(($sx + $tx) / 2.0, $mid);
        }

        $previous = $points[\count($points) - 2];

        return new OrthogonalConnection(
            $start,
            $end,
            $points,
            OrthogonalConnection::segmentsForPoints($points),
            $labelPoint,
            new Point($tx - $previous->x, $ty - $previous->y),
        );
    }
}
