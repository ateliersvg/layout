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
use Atelier\Layout\Distribution;
use Atelier\Layout\Element\Frame;
use Atelier\Layout\Element\Grid;
use Atelier\Layout\Element\Group;
use Atelier\Layout\Element\Spacer;
use Atelier\Layout\Element\Stack;
use Atelier\Layout\Element\TextBlock;
use Atelier\Layout\Geometry\Bounds;
use Atelier\Layout\Geometry\Insets;
use Atelier\Layout\Geometry\Rect;
use Atelier\Layout\LayoutContext;
use Atelier\Layout\Value\InsetSpec;

$context = new LayoutContext();

$toolbar = Stack::row('toolbar')
    ->padding(InsetSpec::percent(4))
    ->gap(10)
    ->alignItems(Alignment::Center)
    ->distribute(Distribution::SpaceBetween)
    ->add(Frame::fixed('left', 40, 20))
    ->add(Frame::fixed('middle', 30, 30))
    ->add(Frame::fixed('right', 50, 20));

$toolbarResult = $toolbar->solve($context, Rect::fromSize(200, 80));

$grid = Grid::columns('grid', 2)
    ->rows([Atelier\Layout\Grid\TrackSize::fixed(32), Atelier\Layout\Grid\TrackSize::fr()])
    ->padding(InsetSpec::px(8))
    ->gap(12)
    ->align(Alignment::Center, Alignment::Center)
    ->add(TextBlock::of('a', 'First wrapped label', 12), columnSpan: 2)
    ->add(Frame::fixed('b', 20, 20))
    ->add(Frame::fixed('c', 32, 16))
    ->add(TextBlock::of('d', 'Bottom item', 12), columnSpan: 2, alignX: Alignment::End);

$gridResult = $grid->solve($context, new Rect(0, 0, 200, 120));

$group = Group::of('group')
    ->add(Frame::fixed('badge', 80, 32))
    ->add(TextBlock::of('label', 'Centered text', 12));

$groupResult = $group->solve($context, Rect::fromSize(160, 90));

$flex = Stack::row('flex')
    ->gap(4)
    ->add(Frame::fixed('start', 20, 20))
    ->add(new Spacer('space'))
    ->add(Frame::fixed('end', 20, 20));

$flexResult = $flex->solve($context, Rect::fromSize(100, 20));
$bounds = Bounds::expand(Bounds::of(new Rect(10, 10, 20, 20), new Rect(40, 30, 10, 10)), Insets::all(4));
$connection = (new OrthogonalConnector())->connect(new Rect(0, 0, 20, 20), new Rect(60, 20, 20, 20));
$text = TextBlock::of('wrapped', 'Bottom snapped text', 12)
    ->align(Alignment::Center, Alignment::End)
    ->layout($context, new Rect(0, 0, 80, 48));

printf("toolbar.left=%s,%s,%s,%s\n", ...formatRectParts($toolbarResult->frameOf('left')));
printf("toolbar.right=%s,%s,%s,%s\n", ...formatRectParts($toolbarResult->frameOf('right')));
printf("grid.d=%s,%s,%s,%s\n", ...formatRectParts($gridResult->frameOf('d')));
printf("group.label=%s,%s,%s,%s\n", ...formatRectParts($groupResult->frameOf('label')));
printf("flex.space=%s,%s,%s,%s\n", ...formatRectParts($flexResult->frameOf('space')));
printf("bounds=%s,%s,%s,%s\n", ...formatRectParts($bounds));
printf("connection.points=%d label=%s,%s\n", \count($connection->points), number_format($connection->labelPoint->x, 2, '.', ''), number_format($connection->labelPoint->y, 2, '.', ''));
printf("text.lines=%d baseline=%s overflow=%s\n", \count($text->lines), number_format($text->lastBaseline() ?? 0.0, 2, '.', ''), $text->hasOverflow() ? 'yes' : 'no');

/**
 * @return array{string, string, string, string}
 */
function formatRectParts(?Rect $rect): array
{
    if (null === $rect) {
        return ['missing', 'missing', 'missing', 'missing'];
    }

    return [
        number_format($rect->x, 2, '.', ''),
        number_format($rect->y, 2, '.', ''),
        number_format($rect->width, 2, '.', ''),
        number_format($rect->height, 2, '.', ''),
    ];
}
