<?php

declare(strict_types=1);

/*
 * Figure framework for the layout illustration generator.
 *
 * Geometry comes from atelier/layout; rendering from atelier/svg. Styling is
 * class-only (no inline colors) -- FIGURE_STYLE is the single source of truth,
 * injected into every figure and written once to figures.css. Scenes (under
 * scenes/) return id => callable(LayoutContext, Rect): list<Drawable> and reuse
 * the Drawable shapes and helpers defined here.
 */

namespace Atelier\Layout\Examples\Figures;

use Atelier\Layout\Geometry\Point;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Result\PlacedNode;
use Atelier\Svg\Element\StyleElement;
use Atelier\Svg\Svg;

const CANVAS = 100.0;
const DISPLAY = 160;
const FRAME_RX = 8.0;
const ITEM_RX = 2.0;

/** The single source of truth for figure styling: classes, never inline colors. */
const FIGURE_STYLE = <<<'CSS'
    .frame             { fill: none; stroke: var(--color, currentColor); stroke-width: 2; opacity: .30; }
    .item              { fill: var(--color, currentColor); opacity: .16; }
    .accent            { fill: var(--accent, currentColor); }
    .dashed            { fill: none; stroke: var(--color, currentColor); stroke-width: 2; stroke-dasharray: 4 3; opacity: .45; }
    .dashed-accent     { fill: none; stroke: var(--accent, currentColor); stroke-width: 2; stroke-dasharray: 4 3; }
    .connection        { fill: none; stroke: var(--color, currentColor); stroke-width: 2; opacity: .55; }
    .connection-accent { fill: none; stroke: var(--accent, currentColor); stroke-width: 2; }
    .arrow             { fill: var(--accent, currentColor); }
    .label             { fill: var(--color, currentColor); opacity: .7; font: 6px sans-serif; }
    CSS;

// ---------------------------------------------------------------------------
// Drawables: each shape knows its own semantic class and how to draw itself.
// ---------------------------------------------------------------------------

interface Drawable
{
    public function draw(Svg $svg): void;
}

/** A rounded rectangle: a container child, a focused item, or an empty slot. */
final readonly class BoxShape implements Drawable
{
    public function __construct(public Rect $rect, public string $class, public float $rx = ITEM_RX)
    {
    }

    public function draw(Svg $svg): void
    {
        $svg->rect($this->rect->x, $this->rect->y, $this->rect->width, $this->rect->height, ['class' => $this->class, 'rx' => $this->rx]);
    }
}

/** An orthogonal link drawn through a polyline of points. */
final readonly class LinkPath implements Drawable
{
    /** @param list<Point> $points */
    public function __construct(public array $points, public string $class)
    {
    }

    public function draw(Svg $svg): void
    {
        $d = '';
        foreach ($this->points as $i => $p) {
            $d .= (0 === $i ? 'M' : ' L').' '.num($p->x).' '.num($p->y);
        }
        $svg->path($d, ['class' => $this->class]);
    }
}

/** A filled triangle at a link's tip, pointing along its tangent. */
final readonly class ArrowHead implements Drawable
{
    public function __construct(public Point $tip, public Point $tangent, public string $class = 'arrow')
    {
    }

    public function draw(Svg $svg): void
    {
        $len = hypot($this->tangent->x, $this->tangent->y) ?: 1.0;
        $ux = $this->tangent->x / $len;
        $uy = $this->tangent->y / $len;
        $size = 5.0;
        $half = 2.6;
        $bx = $this->tip->x - $ux * $size;
        $by = $this->tip->y - $uy * $size;
        $d = 'M '.num($this->tip->x).' '.num($this->tip->y)
            .' L '.num($bx - $uy * $half).' '.num($by + $ux * $half)
            .' L '.num($bx + $uy * $half).' '.num($by - $ux * $half).' Z';
        $svg->path($d, ['class' => $this->class]);
    }
}

// --- Terse drawable factories for scenes ------------------------------------

function box(Rect $rect, string $class): BoxShape
{
    return new BoxShape($rect, $class);
}

/** @param list<Point> $points */
function link(array $points, string $class = 'connection-accent'): LinkPath
{
    return new LinkPath($points, $class);
}

function arrow(Point $tip, Point $tangent): ArrowHead
{
    return new ArrowHead($tip, $tangent);
}

/** Resolve a node frame or fail loudly. */
function frame(PlacedNode $solved, string $id): Rect
{
    $rect = $solved->frameOf($id);
    if (!$rect instanceof Rect) {
        throw new \RuntimeException("missing frame: $id");
    }

    return $rect;
}

/** Accent the focused id, plain item for the rest. @return list<BoxShape> */
function items(PlacedNode $solved, string $accentId, string ...$rest): array
{
    $out = [box(frame($solved, $accentId), 'accent')];
    foreach ($rest as $id) {
        $out[] = box(frame($solved, $id), 'item');
    }

    return $out;
}

function num(float $value): string
{
    return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
}

// ---------------------------------------------------------------------------
// Rendering
// ---------------------------------------------------------------------------

/**
 * Builds one figure with atelier/svg: a frame, the drawables, and the shared
 * stylesheet -- class-only, no inline colors.
 *
 * @param list<Drawable> $drawables
 */
function renderFigure(string $id, Rect $container, array $drawables): Svg
{
    $svg = Svg::create(DISPLAY, DISPLAY);
    $svg->rect($container->x, $container->y, $container->width, $container->height, ['class' => 'frame', 'rx' => FRAME_RX]);

    foreach ($drawables as $drawable) {
        $drawable->draw($svg);
    }

    $root = $svg->getDocument()->getRootElement();
    if (null === $root) {
        throw new \RuntimeException('no root element');
    }
    $root->setAttribute('viewBox', '0 0 '.num(CANVAS).' '.num(CANVAS));
    $root->setAttribute('role', 'img');
    $root->setAttribute('aria-label', ucfirst(str_replace('-', ' ', $id)));
    $root->prependChild((new StyleElement())->setContent("\n".FIGURE_STYLE."\n  "));

    return $svg;
}

/**
 * Render every scene in the registry to $outDir, plus the shared figures.css.
 *
 * @param array<string, callable(\Atelier\Layout\LayoutContext, Rect): list<Drawable>> $registry
 */
function renderAll(array $registry, string $outDir): void
{
    if (!is_dir($outDir) && !mkdir($outDir, 0o755, true) && !is_dir($outDir)) {
        throw new \RuntimeException('Cannot create output dir.');
    }
    file_put_contents($outDir.'/figures.css', ltrim(FIGURE_STYLE)."\n");

    $context = new \Atelier\Layout\LayoutContext();
    $container = Rect::fromSize(CANVAS, CANVAS)->inset(\Atelier\Layout\Geometry\Insets::all(8));

    $ok = 0;
    $errors = [];
    foreach ($registry as $id => $build) {
        try {
            renderFigure($id, $container, $build($context, $container))->savePretty($outDir.'/'.$id.'.svg');
            ++$ok;
        } catch (\Throwable $e) {
            $errors[$id] = $e->getMessage();
        }
    }

    echo "generated $ok / ".\count($registry)." figures into $outDir\n";
    foreach ($errors as $id => $msg) {
        echo "  FAILED $id: $msg\n";
    }
}
