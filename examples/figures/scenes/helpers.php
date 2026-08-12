<?php

declare(strict_types=1);

/*
 * Diagram-helper figures: TrackGroup, InlineGroup, LegendBlock, EdgeBand, AspectFrame.
 * These helpers keep their fluent factories + place(Rect): they return their own
 * result objects, so frames are pulled directly and wrapped with box().
 * Returns id => callable(LayoutContext, Rect): list<Drawable>.
 */

namespace Atelier\Layout\Examples\Figures;

use Atelier\Layout\Alignment;
use Atelier\Layout\Aspect\AspectFrame;
use Atelier\Layout\Band\EdgeBand;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Inline\InlineGroup;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Legend\LegendBlock;
use Atelier\Layout\Track\TrackGroup;
use Atelier\Layout\Value\InsetSpec;

$scenes = [];

// TrackGroup -- equal tracks split the canvas along an axis; one track accented.
$scenes['trackgroup-horizontal'] = static function (LayoutContext $ctx, Rect $c): array {
    $pack = TrackGroup::horizontal('tracks')->equalTracks()->gap(8)->padding(InsetSpec::px(6))
        ->addTrack('l0')->addTrack('l1')->addTrack('l2');
    $layout = $pack->place($c);

    $out = [];
    foreach ($layout->tracks as $track) {
        $out[] = box($track->frame, 'l1' === $track->id ? 'accent' : 'item');
    }

    return $out;
};
$scenes['trackgroup-vertical'] = static function (LayoutContext $ctx, Rect $c): array {
    $pack = TrackGroup::vertical('tracks')->equalTracks()->gap(8)->padding(InsetSpec::px(6))
        ->addTrack('l0')->addTrack('l1')->addTrack('l2');
    $layout = $pack->place($c);

    $out = [];
    foreach ($layout->tracks as $track) {
        $out[] = box($track->frame, 'l0' === $track->id ? 'accent' : 'item');
    }

    return $out;
};

// InlineGroup -- a row of items; equal widths vs content-sized widths; one accented.
$scenes['inlinegroup-equal'] = static function (LayoutContext $ctx, Rect $c): array {
    $row = InlineGroup::equal('row')->gap(8)->padding(InsetSpec::px(6))->align(Alignment::Center)
        ->add('c0')->add('c1')->add('c2');
    $layout = $row->place($c);

    $out = [];
    foreach ($layout->items as $item) {
        $out[] = box($item->frame, 'c1' === $item->id ? 'accent' : 'item');
    }

    return $out;
};
$scenes['inlinegroup-content'] = static function (LayoutContext $ctx, Rect $c): array {
    $row = InlineGroup::contentSized('row')->gap(8)->padding(InsetSpec::px(6))->align(Alignment::Center)
        ->add('c0', preferredWidth: 16)->add('c1', preferredWidth: 32)->add('c2', preferredWidth: 20);
    $layout = $row->place($c);

    $out = [];
    foreach ($layout->items as $item) {
        $out[] = box($item->frame, 'c1' === $item->id ? 'accent' : 'item');
    }

    return $out;
};

// LegendBlock -- swatch + label entries; one swatch accented, labels as items.
$scenes['legend-vertical'] = static function (LayoutContext $ctx, Rect $c): array {
    $legend = LegendBlock::vertical('legend')->swatchSize(12, 12)->labelGap(6)->gap(10)
        ->padding(InsetSpec::px(6))->align(Alignment::Start, Alignment::Center)
        ->add('e0', 36, 9)->add('e1', 36, 9)->add('e2', 36, 9);
    $layout = $legend->place($c);

    $out = [];
    foreach ($layout->entries as $entry) {
        $out[] = box($entry->swatchFrame, 'e0' === $entry->id ? 'accent' : 'item');
        $out[] = box($entry->labelFrame, 'item');
    }

    return $out;
};
$scenes['legend-horizontal'] = static function (LayoutContext $ctx, Rect $c): array {
    $legend = LegendBlock::horizontal('legend')->swatchSize(12, 12)->labelGap(5)->gap(10)
        ->padding(InsetSpec::px(6))->align(Alignment::Center, Alignment::Center)
        ->add('e0', 14, 9)->add('e1', 14, 9)->add('e2', 14, 9);
    $layout = $legend->place($c);

    $out = [];
    foreach ($layout->entries as $entry) {
        $out[] = box($entry->swatchFrame, 'e1' === $entry->id ? 'accent' : 'item');
        $out[] = box($entry->labelFrame, 'item');
    }

    return $out;
};

// EdgeBand -- a dashed edge band above an item body.
$scenes['edgeband'] = static function (LayoutContext $ctx, Rect $c): array {
    $band = EdgeBand::top('title')->bandSize(20)->gap(8)->padding(InsetSpec::px(6));
    $layout = $band->place($c);

    return [box($layout->bandFrame, 'dashed-accent'), box($layout->contentFrame, 'item')];
};

// AspectFrame -- a ratio-fitted fitted frame (accent) letterboxed inside its slot (dashed).
$scenes['aspectframe'] = static function (LayoutContext $ctx, Rect $c): array {
    $frame = AspectFrame::of('frame', 16, 9)->padding(InsetSpec::px(8));
    $layout = $frame->place($c);

    return [box($layout->contentFrame, 'dashed'), box($layout->fittedFrame, 'accent')];
};

return $scenes;
