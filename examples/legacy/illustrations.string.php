<?php

declare(strict_types=1);

/*
 * Generates layout-feature illustration SVGs programmatically.
 *
 * Geometry is computed by atelier/layout (Stack, Group, Grid, Overlay, Spacer).
 * Rendering is local string helpers -- layout never emits SVG, so this tooling only
 * autoloads atelier/layout. Output: layout/docs/images/<feature>.svg
 *
 * Color contract (inline SVG): .frame/.item use var(--color, currentColor) (items at
 * .16 opacity = theme-adaptive gray); .accent uses var(--accent, currentColor).
 * Monochrome by default; pass --accent from the HTML to light the highlighted item.
 */

$layoutSrc = __DIR__.'/../src';
spl_autoload_register(static function (string $class) use ($layoutSrc): void {
    $prefix = 'Atelier\\Layout\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $path = $layoutSrc.'/'.str_replace('\\', '/', substr($class, \strlen($prefix))).'.php';
    if (is_file($path)) {
        require $path;
    }
});

use Atelier\Layout\Alignment;
use Atelier\Layout\Anchor;
use Atelier\Layout\Distribution;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Grid;
use Atelier\Layout\Element\Group;
use Atelier\Layout\Element\Overlay;
use Atelier\Layout\Element\Spacer;
use Atelier\Layout\Element\Stack;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Result\PlacedNode;
use Atelier\Layout\Value\InsetSpec;

const CANVAS = 100.0;
const FRAME_RX = 8.0;
const ITEM_RX = 2.0;

$ctx = new LayoutContext();
$container = Rect::fromSize(CANVAS, CANVAS)->inset(Insets::all(8));

/** resolve a node frame or fail loudly */
function frame(PlacedNode $s, string $id): Rect
{
    $r = $s->frameOf($id);
    if (!$r instanceof Rect) {
        throw new RuntimeException("missing frame: $id");
    }

    return $r;
}

/** @return list<array{rect: Rect, accent: bool}> */
function items(PlacedNode $s, string $accentId, string ...$rest): array
{
    $out = [['rect' => frame($s, $accentId), 'accent' => true]];
    foreach ($rest as $id) {
        $out[] = ['rect' => frame($s, $id), 'accent' => false];
    }

    return $out;
}

// ---------------------------------------------------------------------------
// Variant registry: id => callable(LayoutContext, Rect): list<{rect, accent}>
// ---------------------------------------------------------------------------
$registry = [];

// Alignment -- the 9-grid, via Group aligning a small packed group.
$alignMap = [
    'start' => Alignment::Start, 'center' => Alignment::Center, 'end' => Alignment::End,
];
$rowLabel = ['start' => 'top', 'center' => 'middle', 'end' => 'bottom'];
$colLabel = ['start' => 'left', 'center' => 'center', 'end' => 'right'];
foreach (['start', 'center', 'end'] as $vy) {
    foreach (['start', 'center', 'end'] as $vx) {
        $id = 'align-'.$rowLabel[$vy].'-'.$colLabel[$vx];
        $ax = $alignMap[$vx];
        $ay = $alignMap[$vy];
        $registry[$id] = static function (LayoutContext $ctx, Rect $c) use ($ax, $ay): array {
            $group = Stack::horizontal('row')->gap(4)
                ->add(Frame::fixed('it0', 15, 15))->add(Frame::fixed('it1', 15, 15))->add(Frame::fixed('it2', 15, 15));
            $s = Group::centered('root')->align($ax, $ay)->padding(InsetSpec::px(8))->add($group)->solve($ctx, $c);

            return items($s, 'it0', 'it1', 'it2');
        };
    }
}

// Stack -- one per direction.
foreach (['row' => true, 'column' => false] as $dir => $horizontal) {
    $registry['stack-'.$dir] = static function (LayoutContext $ctx, Rect $c) use ($horizontal): array {
        [$w, $h] = $horizontal ? [18.0, 44.0] : [44.0, 18.0];
        $stack = ($horizontal ? Stack::horizontal('row') : Stack::vertical('row'))
            ->gap(8)->align(Alignment::Center)->distribute(Distribution::Center)
            ->add(Frame::fixed('it0', $w, $h))->add(Frame::fixed('it1', $w, $h))->add(Frame::fixed('it2', $w, $h));

        return items($stack->solve($ctx, $c), 'it0', 'it1', 'it2');
    };
}

// Distribution -- along the main axis (horizontal).
foreach ([
    'start' => Distribution::Start, 'center' => Distribution::Center, 'end' => Distribution::End,
    'space-between' => Distribution::SpaceBetween, 'space-around' => Distribution::SpaceAround,
    'space-evenly' => Distribution::SpaceEvenly,
] as $name => $dist) {
    $registry['distribute-'.$name] = static function (LayoutContext $ctx, Rect $c) use ($dist): array {
        $stack = Stack::horizontal('row')->gap(0)->align(Alignment::Center)->distribute($dist)->padding(InsetSpec::px(6))
            ->add(Frame::fixed('it0', 16, 40))->add(Frame::fixed('it1', 16, 40))->add(Frame::fixed('it2', 16, 40));

        return items($stack->solve($ctx, $c), 'it0', 'it1', 'it2');
    };
}

// Cross-axis alignment -- items of varying height in a horizontal stack.
foreach (['start' => Alignment::Start, 'center' => Alignment::Center, 'end' => Alignment::End, 'stretch' => Alignment::Stretch] as $name => $al) {
    $registry['align-cross-'.$name] = static function (LayoutContext $ctx, Rect $c) use ($al): array {
        $mk = static fn (string $id, float $h) => Alignment::Stretch === $al
            ? Frame::preferred($id, 16, $h) : Frame::fixed($id, 16, $h);
        $stack = Stack::horizontal('row')->gap(8)->align($al)->distribute(Distribution::Center)->padding(InsetSpec::px(6))
            ->add($mk('it0', 24))->add($mk('it1', 44))->add($mk('it2', 32));

        return items($stack->solve($ctx, $c), 'it0', 'it1', 'it2');
    };
}

// Gap -- tight vs loose.
foreach (['none' => 0.0, 'loose' => 14.0] as $name => $gap) {
    $registry['stack-gap-'.$name] = static function (LayoutContext $ctx, Rect $c) use ($gap): array {
        $stack = Stack::horizontal('row')->gap($gap)->align(Alignment::Center)->distribute(Distribution::Center)->padding(InsetSpec::px(6))
            ->add(Frame::fixed('it0', 16, 40))->add(Frame::fixed('it1', 16, 40))->add(Frame::fixed('it2', 16, 40));

        return items($stack->solve($ctx, $c), 'it0', 'it1', 'it2');
    };
}

// Group -- a packed group centered in the box.
$registry['group'] = static function (LayoutContext $ctx, Rect $c): array {
    $group = Stack::horizontal('row')->gap(4)
        ->add(Frame::fixed('it0', 16, 16))->add(Frame::fixed('it1', 16, 16))->add(Frame::fixed('it2', 16, 16));

    return items(Group::centered('root')->align(Alignment::Center, Alignment::Center)->add($group)->solve($ctx, $c), 'it0', 'it1', 'it2');
};

// Grid -- 2x2, 3 columns, and a column span.
$registry['grid-2x2'] = static function (LayoutContext $ctx, Rect $c): array {
    $g = Grid::tracks('g', [TrackSize::fr(), TrackSize::fr()], [TrackSize::fr(), TrackSize::fr()])
        ->gap(8)->padding(InsetSpec::px(6))->align(Alignment::Stretch, Alignment::Stretch)
        ->add(Frame::preferred('it0', 10, 10))->add(Frame::preferred('it1', 10, 10))
        ->add(Frame::preferred('it2', 10, 10))->add(Frame::preferred('it3', 10, 10));

    return items($g->solve($ctx, $c), 'it0', 'it1', 'it2', 'it3');
};
$registry['grid-3-columns'] = static function (LayoutContext $ctx, Rect $c): array {
    $g = Grid::tracks('g', [TrackSize::fr(), TrackSize::fr(), TrackSize::fr()])
        ->gap(8)->padding(InsetSpec::px(6))->align(Alignment::Stretch, Alignment::Stretch)
        ->add(Frame::preferred('it0', 10, 10))->add(Frame::preferred('it1', 10, 10))->add(Frame::preferred('it2', 10, 10));

    return items($g->solve($ctx, $c), 'it0', 'it1', 'it2');
};
$registry['grid-column-span'] = static function (LayoutContext $ctx, Rect $c): array {
    $g = Grid::tracks('g', [TrackSize::fr(), TrackSize::fr()], [TrackSize::fr(), TrackSize::fr()])
        ->gap(8)->padding(InsetSpec::px(6))->align(Alignment::Stretch, Alignment::Stretch)
        ->add(Frame::preferred('it0', 10, 10), columnSpan: 2)
        ->add(Frame::preferred('it1', 10, 10))->add(Frame::preferred('it2', 10, 10));

    return items($g->solve($ctx, $c), 'it0', 'it1', 'it2');
};

// Overlay -- centered, and a corner badge.
$registry['overlay-center'] = static function (LayoutContext $ctx, Rect $c): array {
    $o = Overlay::anchored('o')
        ->add(Frame::preferred('base', 60, 60), Anchor::Center, Anchor::Center)
        ->add(Frame::fixed('it0', 26, 26), Anchor::Center, Anchor::Center);
    $s = $o->solve($ctx, $c);

    return [['rect' => frame($s, 'base'), 'accent' => false], ['rect' => frame($s, 'it0'), 'accent' => true]];
};
$registry['overlay-badge'] = static function (LayoutContext $ctx, Rect $c): array {
    $o = Overlay::anchored('o')->padding(InsetSpec::px(6))
        ->add(Frame::preferred('base', 72, 72), Anchor::Center, Anchor::Center)
        ->add(Frame::fixed('it0', 22, 22), Anchor::TopRight, Anchor::TopRight, offsetX: -6, offsetY: 6);
    $s = $o->solve($ctx, $c);

    return [['rect' => frame($s, 'base'), 'accent' => false], ['rect' => frame($s, 'it0'), 'accent' => true]];
};

// Spacer -- pushes items to opposite ends of a stack.
$registry['spacer'] = static function (LayoutContext $ctx, Rect $c): array {
    $stack = Stack::horizontal('row')->align(Alignment::Center)
        ->add(Frame::fixed('it0', 20, 40))->add(new Spacer('sp'))->add(Frame::fixed('it1', 20, 40));

    return items($stack->solve($ctx, $c), 'it0', 'it1');
};

// ---------------------------------------------------------------------------
// Render
// ---------------------------------------------------------------------------
$outDir = __DIR__.'/../docs/images';
if (!is_dir($outDir) && !mkdir($outDir, 0o755, true) && !is_dir($outDir)) {
    throw new RuntimeException('Cannot create output dir.');
}

$ok = 0;
$errors = [];
foreach ($registry as $id => $build) {
    try {
        $drawables = $build($ctx, $container);
        file_put_contents($outDir.'/'.$id.'.svg', renderSvg($id, $container, $drawables));
        ++$ok;
    } catch (Throwable $e) {
        $errors[$id] = $e->getMessage();
    }
}

echo "generated $ok / ".\count($registry)." illustrations into layout/docs/images/\n";
foreach ($errors as $id => $msg) {
    echo "  FAILED $id: $msg\n";
}

// ---------------------------------------------------------------------------
// Rendering helpers
// ---------------------------------------------------------------------------

/** @param list<array{rect: Rect, accent: bool}> $drawables */
function renderSvg(string $id, Rect $container, array $drawables): string
{
    $body = ['<rect class="frame" x="'.num($container->x).'" y="'.num($container->y).'" width="'.num($container->width).'" height="'.num($container->height).'" rx="'.num(FRAME_RX).'"/>'];
    foreach ($drawables as $d) {
        $r = $d['rect'];
        $body[] = '<rect class="'.($d['accent'] ? 'accent' : 'item').'" x="'.num($r->x).'" y="'.num($r->y).'" width="'.num($r->width).'" height="'.num($r->height).'" rx="'.num(ITEM_RX).'"/>';
    }

    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.num(CANVAS).' '.num(CANVAS).'" width="160" height="160" role="img" aria-label="'.htmlspecialchars(ucfirst(str_replace('-', ' ', $id)), ENT_QUOTES).'">'."\n"
        ."  <style>\n"
        ."    .frame  { fill: none; stroke: var(--color, currentColor); stroke-width: 2; opacity: .30; }\n"
        ."    .item   { fill: var(--color, currentColor); opacity: .16; }\n"
        ."    .accent { fill: var(--accent, currentColor); }\n"
        ."  </style>\n"
        .'  '.implode("\n  ", $body)."\n"
        .'</svg>'."\n";
}

function num(float $v): string
{
    return rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');
}
