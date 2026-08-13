<?php

declare(strict_types=1);

/*
 * Composition figures: Stack, Grid, Group, Overlay, Spacer.
 * Returns id => callable(LayoutContext, Rect): list<Drawable>.
 */

namespace Atelier\Layout\Examples\Figures;

use Atelier\Layout\Alignment;
use Atelier\Layout\Anchor;
use Atelier\Layout\Distribution;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Grid;
use Atelier\Layout\Element\Group;
use Atelier\Layout\Element\Overlay;
use Atelier\Layout\Element\Spacer;
use Atelier\Layout\Element\Stack;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Value\InsetSpec;

$scenes = [];

// Alignment -- the 9-grid, a packed group aligned inside its box.
$alignMap = ['start' => Alignment::Start, 'center' => Alignment::Center, 'end' => Alignment::End];
$rowLabel = ['start' => 'top', 'center' => 'middle', 'end' => 'bottom'];
$colLabel = ['start' => 'left', 'center' => 'center', 'end' => 'right'];
foreach (['start', 'center', 'end'] as $vy) {
    foreach (['start', 'center', 'end'] as $vx) {
        $ax = $alignMap[$vx];
        $ay = $alignMap[$vy];
        $scenes['align-'.$rowLabel[$vy].'-'.$colLabel[$vx]] = static function (LayoutContext $ctx, Rect $c) use ($ax, $ay): array {
            $group = Stack::row('row')->gap(4)
                ->add(Frame::fixed('it0', 15, 15))->add(Frame::fixed('it1', 15, 15))->add(Frame::fixed('it2', 15, 15));
            $solved = Group::of('root')->align($ax, $ay)->padding(InsetSpec::px(8))->add($group)->solve($ctx, $c);

            return items($solved, 'it0', 'it1', 'it2');
        };
    }
}

// Stack -- one per direction; first item accented to read the order.
foreach (['row' => true, 'column' => false] as $dir => $horizontal) {
    $scenes['stack-'.$dir] = static function (LayoutContext $ctx, Rect $c) use ($horizontal): array {
        [$w, $h] = $horizontal ? [18.0, 44.0] : [44.0, 18.0];
        $stack = ($horizontal ? Stack::row('row') : Stack::column('row'))
            ->gap(8)->alignItems(Alignment::Center)->distribute(Distribution::Center)
            ->add(Frame::fixed('it0', $w, $h))->add(Frame::fixed('it1', $w, $h))->add(Frame::fixed('it2', $w, $h));

        return items($stack->solve($ctx, $c), 'it0', 'it1', 'it2');
    };
}

// Distribution -- along the main axis.
foreach ([
    'start' => Distribution::Start, 'center' => Distribution::Center, 'end' => Distribution::End,
    'space-between' => Distribution::SpaceBetween, 'space-around' => Distribution::SpaceAround, 'space-evenly' => Distribution::SpaceEvenly,
] as $name => $dist) {
    $scenes['distribute-'.$name] = static function (LayoutContext $ctx, Rect $c) use ($dist): array {
        $stack = Stack::row('row')->gap(0)->alignItems(Alignment::Center)->distribute($dist)->padding(InsetSpec::px(6))
            ->add(Frame::fixed('it0', 16, 40))->add(Frame::fixed('it1', 16, 40))->add(Frame::fixed('it2', 16, 40));

        return items($stack->solve($ctx, $c), 'it0', 'it1', 'it2');
    };
}

// Cross-axis alignment -- items of varying height in a row.
foreach (['start' => Alignment::Start, 'center' => Alignment::Center, 'end' => Alignment::End, 'stretch' => Alignment::Stretch] as $name => $al) {
    $scenes['align-cross-'.$name] = static function (LayoutContext $ctx, Rect $c) use ($al): array {
        $mk = static fn (string $id, float $h) => Alignment::Stretch === $al ? Frame::preferred($id, 16, $h) : Frame::fixed($id, 16, $h);
        $stack = Stack::row('row')->gap(8)->alignItems($al)->distribute(Distribution::Center)->padding(InsetSpec::px(6))
            ->add($mk('it0', 24))->add($mk('it1', 44))->add($mk('it2', 32));

        return items($stack->solve($ctx, $c), 'it0', 'it1', 'it2');
    };
}

// Gap -- tight vs loose.
foreach (['none' => 0.0, 'loose' => 14.0] as $name => $gap) {
    $scenes['stack-gap-'.$name] = static function (LayoutContext $ctx, Rect $c) use ($gap): array {
        $stack = Stack::row('row')->gap($gap)->alignItems(Alignment::Center)->distribute(Distribution::Center)->padding(InsetSpec::px(6))
            ->add(Frame::fixed('it0', 16, 40))->add(Frame::fixed('it1', 16, 40))->add(Frame::fixed('it2', 16, 40));

        return items($stack->solve($ctx, $c), 'it0', 'it1', 'it2');
    };
}

// Group -- a compact group centered in the box.
$scenes['group'] = static function (LayoutContext $ctx, Rect $c): array {
    $group = Stack::row('row')->gap(4)->add(Frame::fixed('it0', 16, 16))->add(Frame::fixed('it1', 16, 16))->add(Frame::fixed('it2', 16, 16));

    return items(Group::of('root')->align(Alignment::Center, Alignment::Center)->add($group)->solve($ctx, $c), 'it0', 'it1', 'it2');
};

// Grid -- structures, span, and an empty (dashed) slot.
$scenes['grid-2x2'] = static function (LayoutContext $ctx, Rect $c): array {
    $g = Grid::tracks('g', [TrackSize::fr(), TrackSize::fr()], [TrackSize::fr(), TrackSize::fr()])
        ->gap(8)->padding(InsetSpec::px(6))->align(Alignment::Stretch, Alignment::Stretch)
        ->add(Frame::preferred('it0', 10, 10))->add(Frame::preferred('it1', 10, 10))->add(Frame::preferred('it2', 10, 10))->add(Frame::preferred('it3', 10, 10));

    return items($g->solve($ctx, $c), 'it0', 'it1', 'it2', 'it3');
};
$scenes['grid-3-columns'] = static function (LayoutContext $ctx, Rect $c): array {
    $g = Grid::columns('g', 3)->gap(8)->padding(InsetSpec::px(6))->align(Alignment::Stretch, Alignment::Stretch)
        ->add(Frame::preferred('it0', 10, 10))->add(Frame::preferred('it1', 10, 10))->add(Frame::preferred('it2', 10, 10));

    return items($g->solve($ctx, $c), 'it0', 'it1', 'it2');
};
$scenes['grid-column-span'] = static function (LayoutContext $ctx, Rect $c): array {
    $g = Grid::tracks('g', [TrackSize::fr(), TrackSize::fr()], [TrackSize::fr(), TrackSize::fr()])
        ->gap(8)->padding(InsetSpec::px(6))->align(Alignment::Stretch, Alignment::Stretch)
        ->add(Frame::preferred('it0', 10, 10), columnSpan: 2)->add(Frame::preferred('it1', 10, 10))->add(Frame::preferred('it2', 10, 10));

    return items($g->solve($ctx, $c), 'it0', 'it1', 'it2');
};
$scenes['grid-row-span'] = static function (LayoutContext $ctx, Rect $c): array {
    $g = Grid::tracks('g', [TrackSize::fr(), TrackSize::fr()], [TrackSize::fr(), TrackSize::fr()])
        ->gap(8)->padding(InsetSpec::px(6))->align(Alignment::Stretch, Alignment::Stretch)
        ->add(Frame::preferred('it0', 10, 10), rowSpan: 2)->add(Frame::preferred('it1', 10, 10))->add(Frame::preferred('it2', 10, 10));

    return items($g->solve($ctx, $c), 'it0', 'it1', 'it2');
};
$scenes['grid-dashed-slot'] = static function (LayoutContext $ctx, Rect $c): array {
    $g = Grid::tracks('g', [TrackSize::fr(), TrackSize::fr()], [TrackSize::fr(), TrackSize::fr()])
        ->gap(8)->padding(InsetSpec::px(6))->align(Alignment::Stretch, Alignment::Stretch)
        ->add(Frame::preferred('it0', 10, 10))->add(Frame::preferred('it1', 10, 10))->add(Frame::preferred('it2', 10, 10))->add(Frame::preferred('slot', 10, 10));
    $s = $g->solve($ctx, $c);

    return [box(frame($s, 'it0'), 'accent'), box(frame($s, 'it1'), 'item'), box(frame($s, 'it2'), 'item'), box(frame($s, 'slot'), 'dashed')];
};

// Overlay -- centered, and a corner badge.
$scenes['overlay-center'] = static function (LayoutContext $ctx, Rect $c): array {
    $o = Overlay::of('o')->add(Frame::preferred('base', 60, 60))->add(Frame::fixed('it0', 26, 26));
    $s = $o->solve($ctx, $c);

    return [box(frame($s, 'base'), 'item'), box(frame($s, 'it0'), 'accent')];
};
$scenes['overlay-badge'] = static function (LayoutContext $ctx, Rect $c): array {
    $o = Overlay::of('o')->padding(InsetSpec::px(6))
        ->add(Frame::preferred('base', 72, 72))
        ->add(Frame::fixed('it0', 22, 22), Anchor::TopRight, Anchor::TopRight, offsetX: -6, offsetY: 6);
    $s = $o->solve($ctx, $c);

    return [box(frame($s, 'base'), 'item'), box(frame($s, 'it0'), 'accent')];
};

// Spacer -- pushes items to opposite ends.
$scenes['spacer'] = static function (LayoutContext $ctx, Rect $c): array {
    $stack = Stack::row('row')->alignItems(Alignment::Center)
        ->add(Frame::fixed('it0', 20, 40))->add(new Spacer('sp'))->add(Frame::fixed('it1', 20, 40));

    return items($stack->solve($ctx, $c), 'it0', 'it1');
};

return $scenes;
