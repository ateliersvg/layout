<?php

declare(strict_types=1);

/*
 * Geometry figures: Fit modes, BoxConstraints, Bounds union, Insets, GroupBounds.
 * Returns id => callable(LayoutContext, Rect): list<Drawable>.
 */

namespace Atelier\Layout\Examples\Figures;

use Atelier\Layout\Constraint\BoxConstraints;
use Atelier\Layout\Fit\Fit;
use Atelier\Layout\Fit\FitMode;
use Atelier\Layout\Geometry\Bounds;
use Atelier\Layout\Geometry\GroupBounds;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Geometry\Size;
use Atelier\Layout\LayoutContext;

$scenes = [];

// Fit -- a source size fitted into a square target box (dashed), result accented.
// Source aspect differs from the target so each mode reads distinctly.
$fitTarget = new Rect(24.0, 24.0, 52.0, 52.0);
foreach ([
    'contain' => [new Size(60.0, 40.0), FitMode::Contain],   // letterboxed inside the box
    'cover' => [new Size(60.0, 40.0), FitMode::Cover],        // fills the box, overflows one axis
    'scale-down' => [new Size(30.0, 20.0), FitMode::ScaleDown], // small source kept at natural size
    'fill' => [new Size(60.0, 40.0), FitMode::Fill],          // stretched to the box exactly
] as $name => [$source, $mode]) {
    $scenes['fit-'.$name] = static function (LayoutContext $ctx, Rect $c) use ($fitTarget, $source, $mode): array {
        $fitted = Fit::rect($source, $fitTarget, $mode);

        return [box($fitted, 'accent'), box($fitTarget, 'dashed')];
    };
}

// BoxConstraints -- the constraint box (dashed) and the constrained content (accent).
$constraintBox = new Rect(24.0, 24.0, 52.0, 52.0);
$desired = new Size(30.0, 20.0);
// Tight: content is forced to the exact size -- accent fills the box.
$scenes['constraints-tight'] = static function (LayoutContext $ctx, Rect $c) use ($constraintBox, $desired): array {
    $size = BoxConstraints::tight($constraintBox->width, $constraintBox->height)->constrain($desired);

    return [box(new Rect($constraintBox->x, $constraintBox->y, $size->width, $size->height), 'accent'), box($constraintBox, 'dashed')];
};
// Loose: content keeps its smaller desired size under a max bound.
$scenes['constraints-loose'] = static function (LayoutContext $ctx, Rect $c) use ($constraintBox, $desired): array {
    $size = (new BoxConstraints(maxWidth: $constraintBox->width, maxHeight: $constraintBox->height))->constrain($desired);

    return [box(new Rect($constraintBox->x, $constraintBox->y, $size->width, $size->height), 'accent'), box($constraintBox, 'dashed')];
};

// Bounds -- scattered item rects and their union as a dashed-accent outline.
$scenes['bounds-union'] = static function (LayoutContext $ctx, Rect $c): array {
    $rects = [
        new Rect(20.0, 22.0, 18.0, 16.0),
        new Rect(54.0, 30.0, 16.0, 20.0),
        new Rect(34.0, 56.0, 22.0, 14.0),
    ];
    $union = Bounds::fromRects($rects);
    if (!$union instanceof Rect) {
        return [];
    }
    $out = [box($union, 'dashed-accent')];
    foreach ($rects as $r) {
        $out[] = box($r, 'item');
    }

    return $out;
};

// Insets -- outer box (dashed) and the padded content (accent) via Rect::inset.
$scenes['insets-padding'] = static function (LayoutContext $ctx, Rect $c): array {
    $outer = new Rect(20.0, 20.0, 60.0, 60.0);

    return [box($outer->inset(Insets::all(12.0)), 'accent'), box($outer, 'dashed')];
};

// GroupBounds -- input frames (items) and the placed group bounds (dashed-accent).
$scenes['group-bounds'] = static function (LayoutContext $ctx, Rect $c): array {
    $frames = [
        'a' => new Rect(28.0, 30.0, 16.0, 14.0),
        'b' => new Rect(50.0, 34.0, 14.0, 18.0),
        'c' => new Rect(36.0, 54.0, 18.0, 12.0),
    ];
    $groupBounds = GroupBounds::fromFrames('group', $frames)->padding(Insets::all(6.0))->frame();
    if (!$groupBounds instanceof Rect) {
        return [];
    }
    $out = [box($groupBounds, 'dashed-accent')];
    foreach ($frames as $r) {
        $out[] = box($r, 'item');
    }

    return $out;
};

return $scenes;
