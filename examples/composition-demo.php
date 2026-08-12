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
use Atelier\Layout\Connection\OrthogonalConnector;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Grid;
use Atelier\Layout\Element\TextBlock;
use Atelier\Layout\Geometry\Bounds;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\Grid\TrackSize;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Value\InsetSpec;

$context = new LayoutContext();
$canvas = Rect::fromSize(360, 220);
$margin = Insets::all(16);
$page = $canvas->inset($margin);

$grid = Grid::tracks('dashboard', [TrackSize::fr(), TrackSize::fr()], [TrackSize::fixed(44), TrackSize::fr()])
    ->padding(InsetSpec::px(12))
    ->gap(12)
    ->align(Alignment::Stretch, Alignment::Stretch)
    ->add(TextBlock::of('title', 'Pipeline status overview', 14), columnSpan: 2, alignX: Alignment::Center, alignY: Alignment::Center)
    ->add(Frame::preferred('build', 120, 72))
    ->add(Frame::preferred('deploy', 120, 72));

$result = $grid->solve($context, $page);
$titleText = TextBlock::of('title.text', 'Pipeline status overview', 14)
    ->align(Alignment::Center, Alignment::Center)
    ->layout($context, $result->frameOf('title') ?? $page);
$buildLabel = TextBlock::of('build.label', 'Build queue', 12)
    ->align(Alignment::Center, Alignment::End)
    ->layout($context, $result->frameOf('build') ?? $page);
$deployLabel = TextBlock::of('deploy.label', 'Deploy target wraps', 12)
    ->align(Alignment::Center, Alignment::End)
    ->layout($context, $result->frameOf('deploy') ?? $page);

$build = $result->frameOf('build');
$deploy = $result->frameOf('deploy');
$title = $result->frameOf('title');
if (!$build instanceof Rect || !$deploy instanceof Rect || !$title instanceof Rect) {
    throw new RuntimeException('Demo frames are missing.');
}

$groupBounds = Bounds::expand(Bounds::of($build, $deploy), Insets::all(8));
$connection = (new OrthogonalConnector())->connect($title, $deploy);

$svg = svg(
    $canvas,
    [
        rectNode($canvas, '#ffffff', '#94a3b8', 'canvas'),
        rectNode($page, '#f8fafc', '#64748b', 'margin box'),
        rectNode($groupBounds, 'none', '#0f766e', 'group bounds'),
        rectNode($result->frameOf('title'), '#e0f2fe', '#0284c7', 'grid title slot'),
        rectNode($build, '#dcfce7', '#16a34a', 'build slot'),
        rectNode($deploy, '#fee2e2', '#dc2626', 'deploy slot'),
        textNodes($titleText),
        textNodes($buildLabel),
        textNodes($deployLabel),
        connectionNode($connection),
    ],
);

$outputDir = __DIR__.'/output';
if (!is_dir($outputDir) && !mkdir($outputDir, 0o755, true)) {
    throw new RuntimeException('Cannot create demo output directory.');
}
$target = __DIR__.'/output/composition-demo.svg';
file_put_contents($target, $svg);

echo 'canvas='.formatRect($canvas)."\n";
echo 'marginBox='.formatRect($page)."\n";
echo 'title='.formatRect($title)."\n";
echo 'build='.formatRect($build)."\n";
echo 'deploy='.formatRect($deploy)."\n";
echo 'groupBounds='.formatRect($groupBounds)."\n";
echo 'connection='.implode(' -> ', array_map(static fn ($point): string => number($point->x).','.number($point->y), $connection->points))."\n";
echo 'wrote='.$target."\n";

/**
 * @param list<string> $nodes
 */
function svg(Rect $canvas, array $nodes): string
{
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.number($canvas->width).'" height="'.number($canvas->height).'" viewBox="0 0 '.number($canvas->width).' '.number($canvas->height).'">'."\n"
        .implode("\n", $nodes)
        ."\n</svg>\n";
}

function rectNode(?Rect $rect, string $fill, string $stroke, string $label): string
{
    if (!$rect instanceof Rect) {
        return '';
    }

    return '<rect x="'.number($rect->x).'" y="'.number($rect->y).'" width="'.number($rect->width).'" height="'.number($rect->height).'" fill="'.$fill.'" stroke="'.$stroke.'" stroke-width="1.5" rx="4"><title>'.htmlspecialchars($label, ENT_QUOTES).'</title></rect>';
}

function connectionNode(Atelier\Layout\Connection\OrthogonalConnection $connection): string
{
    $commands = [];
    foreach ($connection->points as $index => $point) {
        $commands[] = (0 === $index ? 'M' : 'L').' '.number($point->x).' '.number($point->y);
    }

    return '<path d="'.implode(' ', $commands).'" fill="none" stroke="#111827" stroke-width="2"/><circle cx="'.number($connection->labelPoint->x).'" cy="'.number($connection->labelPoint->y).'" r="3" fill="#111827"><title>connection label point</title></circle>';
}

function textNodes(Atelier\Layout\Text\TextLayout $layout): string
{
    $nodes = [];
    foreach ($layout->lines as $line) {
        $nodes[] = '<text x="'.number($line->frame->x + $line->frame->width / 2.0).'" y="'.number($line->baseline).'" font-family="sans-serif" font-size="12" text-anchor="middle" fill="#111827">'.htmlspecialchars($line->text, ENT_QUOTES).'</text>';
    }

    return implode("\n", $nodes);
}

function formatRect(?Rect $rect): string
{
    if (!$rect instanceof Rect) {
        return 'missing';
    }

    return number($rect->x).','.number($rect->y).','.number($rect->width).','.number($rect->height);
}

function number(float $value): string
{
    return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
}
