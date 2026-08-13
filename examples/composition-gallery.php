<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'Atelier\\Layout\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = __DIR__.'/../src/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
    if (is_file($path)) {
        require $path;
    }
});

use Atelier\Layout\Alignment;
use Atelier\Layout\Anchor;
use Atelier\Layout\Connection\OrthogonalConnector;
use Atelier\Layout\Distribution;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Grid;
use Atelier\Layout\Element\Overlay;
use Atelier\Layout\Element\Stack;
use Atelier\Layout\Element\TextBlock;
use Atelier\Layout\Geometry\Bounds;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\LayoutSolver;
use Atelier\Layout\Text\TextLayout;
use Atelier\Layout\Value\InsetSpec;

$outputDir = __DIR__.'/output';
if (!is_dir($outputDir) && !mkdir($outputDir, 0o755, true)) {
    throw new RuntimeException('Cannot create composition gallery output directory.');
}

$context = new LayoutContext(snapStep: 0.5);
$solver = new LayoutSolver($context);

$written = [
    writeDashboardSpecimen($solver, $context, $outputDir),
    writeOverlaySpecimen($solver, $context, $outputDir),
    writeConnectionSpecimen($solver, $context, $outputDir),
];

foreach ($written as $path) {
    echo 'Wrote '.$path.\PHP_EOL;
}

function writeDashboardSpecimen(LayoutSolver $solver, LayoutContext $context, string $outputDir): string
{
    $canvas = Rect::fromSize(760, 420);
    $page = $canvas->inset(Insets::all(28));
    $grid = Grid::tracks('dashboard', [TrackSize::fixed(160), TrackSize::fr(), TrackSize::fr()], [
        TrackSize::fixed(56),
        TrackSize::fr(1.2),
        TrackSize::fr(),
    ])
        ->padding(InsetSpec::px(16))
        ->gap(16)
        ->align(Alignment::Stretch, Alignment::Stretch)
        ->add(TextBlock::of('title', 'Layout-composed dashboard', 18), columnSpan: 3, alignX: Alignment::Start, alignY: Alignment::Center)
        ->add(Frame::stretch('nav'), rowSpan: 2)
        ->add(Frame::stretch('main'), columnSpan: 2)
        ->add(Frame::stretch('queue'))
        ->add(Frame::stretch('detail'));

    $result = $solver->solve($grid, $page);
    $main = requiredFrame($result->frameOf('main'), 'main');
    $queue = requiredFrame($result->frameOf('queue'), 'queue');
    $detail = requiredFrame($result->frameOf('detail'), 'detail');
    $cards = Bounds::expand(Bounds::of($main, $queue, $detail), Insets::all(10));

    $nodes = [
        rect($canvas, '#09090b', null, 0),
        rect($page, '#111827', '#334155', 8),
        rect($cards, 'none', '#22d3ee', 8, 1.5, 0.55),
        rect(requiredFrame($result->frameOf('title'), 'title'), '#0f172a', '#1e293b', 6),
        rect(requiredFrame($result->frameOf('nav'), 'nav'), '#18181b', '#475569', 6),
        rect($main, '#0f766e', '#2dd4bf', 6, 1.5, 0.38),
        rect($queue, '#4c1d95', '#a78bfa', 6, 1.5, 0.45),
        rect($detail, '#7f1d1d', '#fca5a5', 6, 1.5, 0.42),
        label($context, 'title.label', 'Grid tracks, spans, padding, and group bounds', requiredFrame($result->frameOf('title'), 'title'), 18, '#f8fafc', Alignment::Start, Alignment::Center),
        label($context, 'nav.label', "Fixed\nrail", requiredFrame($result->frameOf('nav'), 'nav')->inset(Insets::all(18)), 15, '#cbd5e1', Alignment::Center, Alignment::Center),
        label($context, 'main.label', "Spanning content area\nstretches across two columns", $main->inset(Insets::all(20)), 18, '#ccfbf1', Alignment::Center, Alignment::Center),
        label($context, 'queue.label', "Bottom-snapped\nwrapped label", $queue->inset(Insets::all(16)), 15, '#ede9fe', Alignment::Center, Alignment::End),
        label($context, 'detail.label', "Independent slot\nsame grid solve", $detail->inset(Insets::all(16)), 15, '#fee2e2', Alignment::Center, Alignment::End),
    ];

    $target = $outputDir.'/layout-dashboard.svg';
    file_put_contents($target, svg($canvas, flatten($nodes)));

    return $target;
}

function writeOverlaySpecimen(LayoutSolver $solver, LayoutContext $context, string $outputDir): string
{
    $canvas = Rect::fromSize(640, 360);
    $frame = $canvas->inset(new Insets(34, 38, 34, 38));
    $overlay = Overlay::of('overlay')
        ->padding(InsetSpec::percent(4))
        ->add(Frame::fixed('center-card', 280, 150), Anchor::Center, Anchor::Center)
        ->add(Frame::fixed('top-badge', 128, 34), Anchor::TopCenter, Anchor::TopCenter, 0, 14)
        ->add(Frame::fixed('right-tag', 108, 32), Anchor::CenterRight, Anchor::CenterRight, -18, 0)
        ->add(Frame::fixed('bottom-note', 240, 44), Anchor::BottomCenter, Anchor::BottomCenter, 0, -16);

    $result = $solver->solve($overlay, $frame);
    $center = requiredFrame($result->frameOf('center-card'), 'center-card');
    $badge = requiredFrame($result->frameOf('top-badge'), 'top-badge');
    $tag = requiredFrame($result->frameOf('right-tag'), 'right-tag');
    $note = requiredFrame($result->frameOf('bottom-note'), 'bottom-note');

    $nodes = [
        rect($canvas, '#030712', null, 0),
        rect($frame, '#111827', '#374151', 10),
        rect($center, '#164e63', '#67e8f9', 8, 1.5, 0.65),
        rect($badge, '#f59e0b', null, 6, 1.0, 0.95),
        rect($tag, '#be123c', null, 6, 1.0, 0.95),
        rect($note, '#1f2937', '#94a3b8', 6),
        line($badge->x + $badge->width / 2.0, $badge->y + $badge->height, $center->x + $center->width / 2.0, $center->y, '#fbbf24'),
        label($context, 'overlay.center', "Overlay anchors\nsnap children to a shared rect", $center->inset(Insets::all(18)), 18, '#ecfeff', Alignment::Center, Alignment::Center),
        label($context, 'overlay.badge', 'Top badge', $badge, 13, '#111827', Alignment::Center, Alignment::Center),
        label($context, 'overlay.tag', 'Right tag', $tag, 13, '#fff1f2', Alignment::Center, Alignment::Center),
        label($context, 'overlay.note', 'Padding is computed from the host size', $note->inset(Insets::all(8)), 12, '#cbd5e1', Alignment::Center, Alignment::Center),
    ];

    $target = $outputDir.'/layout-overlays.svg';
    file_put_contents($target, svg($canvas, flatten($nodes)));

    return $target;
}

function writeConnectionSpecimen(LayoutSolver $solver, LayoutContext $context, string $outputDir): string
{
    $canvas = Rect::fromSize(780, 360);
    $page = $canvas->inset(Insets::all(32));
    $stack = Stack::row('pipeline')
        ->padding(InsetSpec::px(18))
        ->gap(24)
        ->alignItems(Alignment::Center)
        ->distribute(Distribution::SpaceBetween)
        ->add(Frame::fixed('ingest', 146, 82))
        ->add(Frame::fixed('normalize', 160, 104))
        ->add(Frame::fixed('review', 146, 82))
        ->add(Frame::fixed('publish', 146, 82));

    $result = $solver->solve($stack, $page);
    $ids = ['ingest', 'normalize', 'review', 'publish'];
    $frames = [];
    foreach ($ids as $id) {
        $frames[$id] = requiredFrame($result->frameOf($id), $id);
    }

    $connector = new OrthogonalConnector();
    $connections = [
        $connector->connect($frames['ingest'], $frames['normalize']),
        $connector->connect($frames['normalize'], $frames['review']),
        $connector->connect($frames['review'], $frames['publish']),
    ];
    $groupBounds = Bounds::expand(Bounds::of(...array_values($frames)), Insets::all(12));

    $nodes = [
        rect($canvas, '#020617', null, 0),
        rect($page, '#0f172a', '#334155', 10),
        rect($groupBounds, 'none', '#38bdf8', 10, 1.5, 0.55),
    ];

    foreach ($connections as $connection) {
        $nodes[] = path($connection, '#e2e8f0');
        $nodes[] = circle($connection->labelPoint->x, $connection->labelPoint->y, 3.5, '#38bdf8');
    }

    foreach ($frames as $id => $frame) {
        $nodes[] = rect($frame, '#1e293b', '#94a3b8', 7);
        $nodes[] = label($context, 'connection.'.$id, ucfirst($id), $frame, 15, '#f8fafc', Alignment::Center, Alignment::Center);
    }

    $target = $outputDir.'/layout-connections-board.svg';
    file_put_contents($target, svg($canvas, flatten($nodes)));

    return $target;
}

/**
 * @param list<string|list<string>> $nodes
 *
 * @return list<string>
 */
function flatten(array $nodes): array
{
    $flat = [];
    foreach ($nodes as $node) {
        foreach ((array) $node as $part) {
            if ('' !== $part) {
                $flat[] = $part;
            }
        }
    }

    return $flat;
}

/**
 * @param list<string> $nodes
 */
function svg(Rect $canvas, array $nodes): string
{
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.num($canvas->width).'" height="'.num($canvas->height).'" viewBox="0 0 '.num($canvas->width).' '.num($canvas->height).'">'."\n"
        .implode("\n", $nodes)
        ."\n</svg>\n";
}

function requiredFrame(?Rect $frame, string $id): Rect
{
    if (!$frame instanceof Rect) {
        throw new RuntimeException(\sprintf('Missing frame "%s".', $id));
    }

    return $frame;
}

function rect(Rect $rect, ?string $fill, ?string $stroke, float $radius, float $strokeWidth = 1.0, ?float $opacity = null): string
{
    $attributes = [
        'x="'.num($rect->x).'"',
        'y="'.num($rect->y).'"',
        'width="'.num($rect->width).'"',
        'height="'.num($rect->height).'"',
        'rx="'.num($radius).'"',
        'fill="'.($fill ?? 'none').'"',
    ];
    if (null !== $stroke) {
        $attributes[] = 'stroke="'.$stroke.'"';
        $attributes[] = 'stroke-width="'.num($strokeWidth).'"';
    }
    if (null !== $opacity) {
        $attributes[] = 'opacity="'.num($opacity).'"';
    }

    return '<rect '.implode(' ', $attributes).'/>';
}

function line(float $x1, float $y1, float $x2, float $y2, string $stroke): string
{
    return '<line x1="'.num($x1).'" y1="'.num($y1).'" x2="'.num($x2).'" y2="'.num($y2).'" stroke="'.$stroke.'" stroke-width="1.5"/>';
}

function circle(float $cx, float $cy, float $r, string $fill): string
{
    return '<circle cx="'.num($cx).'" cy="'.num($cy).'" r="'.num($r).'" fill="'.$fill.'"/>';
}

function path(Atelier\Layout\Connection\OrthogonalConnection $connection, string $stroke): string
{
    $commands = [];
    foreach ($connection->points as $index => $point) {
        $commands[] = (0 === $index ? 'M' : 'L').' '.num($point->x).' '.num($point->y);
    }

    return '<path d="'.implode(' ', $commands).'" fill="none" stroke="'.$stroke.'" stroke-width="2"/>';
}

/**
 * @return list<string>
 */
function label(
    LayoutContext $context,
    string $id,
    string $text,
    Rect $frame,
    float $fontSize,
    string $fill,
    Alignment $alignX,
    Alignment $alignY,
): array {
    $layout = TextBlock::of($id, $text, $fontSize)
        ->align($alignX, $alignY)
        ->breakWords()
        ->layout($context, $frame);

    return text($layout, $fontSize, $fill, $alignX);
}

/**
 * @return list<string>
 */
function text(TextLayout $layout, float $fontSize, string $fill, Alignment $alignX): array
{
    $anchor = match ($alignX) {
        Alignment::Start, Alignment::Stretch => 'start',
        Alignment::Center => 'middle',
        Alignment::End => 'end',
    };

    $nodes = [];
    foreach ($layout->lines as $line) {
        $x = match ($alignX) {
            Alignment::Start, Alignment::Stretch => $line->frame->x,
            Alignment::Center => $line->frame->x + $line->frame->width / 2.0,
            Alignment::End => $line->frame->x + $line->frame->width,
        };
        $nodes[] = '<text x="'.num($x).'" y="'.num($line->baseline).'" font-family="Helvetica, Arial, sans-serif" font-size="'.num($fontSize).'" text-anchor="'.$anchor.'" fill="'.$fill.'">'.htmlspecialchars($line->text, ENT_QUOTES).'</text>';
    }

    return $nodes;
}

function num(float $value): string
{
    return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
}
