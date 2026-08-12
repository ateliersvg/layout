<?php

declare(strict_types=1);

/*
 * Connector figures: orthogonal links between boxes, connection labels, endpoint badges.
 * Returns id => callable(LayoutContext, Rect): list<Drawable>.
 *
 * Convention: source box drawn as 'item', target box as 'dashed', the link via
 * link($connection->points), the arrow at the placed tip, and any placed label or
 * badge frame as 'accent'. Boxes are placed relative to the container Rect $c.
 */

namespace Atelier\Layout\Examples\Figures;

use Atelier\Layout\Connection\ConnectionEndpointBadge;
use Atelier\Layout\Connection\ConnectionEndpointBadgePlacement;
use Atelier\Layout\Connection\ConnectionLabel;
use Atelier\Layout\Connection\ConnectionLabelPlacement;
use Atelier\Layout\Connection\OrthogonalConnection;
use Atelier\Layout\Connection\OrthogonalConnector;
use Atelier\Layout\Connection\Port;
use Atelier\Layout\Connection\PortSide;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Point;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\LayoutContext;

$scenes = [];

$connector = new OrthogonalConnector();

// A straight horizontal connection between two centered boxes -- shared by the label
// and badge figures so the placement is the only thing that changes.
$straightConnection = static function (Rect $c) use ($connector): array {
    $src = new Rect($c->x + 4, $c->y + 34, 20, 16);
    $tgt = new Rect($c->x + 58, $c->y + 34, 20, 16);

    return [$src, $tgt, $connector->connect($src, $tgt)];
};

// connection-straight -- aligned boxes collapse to a single segment.
$scenes['connection-straight'] = static function (LayoutContext $ctx, Rect $c) use ($connector): array {
    $src = new Rect($c->x + 6, $c->y + 34, 24, 16);
    $tgt = new Rect($c->x + 54, $c->y + 34, 24, 16);
    $connection = $connector->connect($src, $tgt);

    return [
        box($src, 'item'),
        box($tgt, 'dashed'),
        link($connection->points),
        arrow($connection->endPoint(), $connection->tipTangent),
    ];
};

// connection-l -- a single right-angle bend (source bottom port to target left port).
$scenes['connection-l'] = static function (LayoutContext $ctx, Rect $c): array {
    $src = new Rect($c->x + 8, $c->y + 8, 24, 16);
    $tgt = new Rect($c->x + 48, $c->y + 44, 24, 16);
    $start = Port::on($src, PortSide::Bottom);
    $end = Port::on($tgt, PortSide::Left);
    $corner = new Point($start->point->x, $end->point->y);
    $points = [$start->point, $corner, $end->point];
    $connection = new OrthogonalConnection(
        $start,
        $end,
        $points,
        OrthogonalConnection::segmentsForPoints($points),
        $corner,
        new Point($end->point->x - $corner->x, $end->point->y - $corner->y),
    );

    return [
        box($src, 'item'),
        box($tgt, 'dashed'),
        link($connection->points),
        arrow($connection->endPoint(), $connection->tipTangent),
    ];
};

// connection-z -- offset boxes get a mid-point Z (horizontal, vertical, horizontal).
$scenes['connection-z'] = static function (LayoutContext $ctx, Rect $c) use ($connector): array {
    $src = new Rect($c->x + 4, $c->y + 14, 24, 16);
    $tgt = new Rect($c->x + 52, $c->y + 50, 24, 16);
    $connection = $connector->connect($src, $tgt);

    return [
        box($src, 'item'),
        box($tgt, 'dashed'),
        link($connection->points),
        arrow($connection->endPoint(), $connection->tipTangent),
    ];
};

// Connection labels -- centered on, above, and below the middle segment.
foreach ([
    'centered' => ConnectionLabelPlacement::Centered,
    'above' => ConnectionLabelPlacement::Above,
    'below' => ConnectionLabelPlacement::Below,
] as $name => $placement) {
    $scenes['connectionlabel-'.$name] = static function (LayoutContext $ctx, Rect $c) use ($straightConnection, $placement): array {
        [$src, $tgt, $connection] = $straightConnection($c);
        $label = ConnectionLabel::for($connection)
            ->size(new Size(24, 10))
            ->padding(Insets::all(4))
            ->placement($placement)
            ->place();

        return [
            box($src, 'item'),
            box($tgt, 'dashed'),
            link($connection->points),
            arrow($connection->endPoint(), $connection->tipTangent),
            box($label->frame, 'accent'),
        ];
    };
}

// Endpoint badges -- a marker pinned to the start or the end of the connection.
foreach ([
    'start' => ConnectionEndpointBadgePlacement::Start,
    'end' => ConnectionEndpointBadgePlacement::End,
] as $name => $placement) {
    $scenes['badge-'.$name] = static function (LayoutContext $ctx, Rect $c) use ($straightConnection, $placement): array {
        [$src, $tgt, $connection] = $straightConnection($c);
        $badge = ConnectionEndpointBadge::for($connection, $placement)
            ->size(new Size(12, 12))
            ->padding(Insets::all(4))
            ->place();

        return [
            box($src, 'item'),
            box($tgt, 'dashed'),
            link($connection->points),
            arrow($connection->endPoint(), $connection->tipTangent),
            box($badge->frame, 'accent'),
        ];
    };
}

return $scenes;
