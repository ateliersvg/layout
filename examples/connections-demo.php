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

use Atelier\Layout\Connection\OrthogonalConnector;
use Atelier\Layout\Geometry\Rect;

$connector = new OrthogonalConnector();

$cases = [
    'left-to-right' => [new Rect(20, 20, 60, 40), new Rect(160, 50, 70, 40)],
    'top-to-bottom' => [new Rect(60, 20, 70, 40), new Rect(80, 140, 70, 40)],
    'right-to-left' => [new Rect(180, 30, 70, 40), new Rect(40, 60, 60, 40)],
];

foreach ($cases as $name => [$from, $to]) {
    $connection = $connector->connect($from, $to);

    echo $name."\n";
    echo '  from='.formatRect($from)."\n";
    echo '  to='.formatRect($to)."\n";
    echo '  points='.implode(' -> ', array_map(static fn ($point): string => number($point->x).','.number($point->y), $connection->points))."\n";
    echo '  label='.number($connection->labelPoint->x).','.number($connection->labelPoint->y)."\n";
    echo '  tangent='.number($connection->tipTangent->x).','.number($connection->tipTangent->y)."\n";
}

function formatRect(Rect $rect): string
{
    return number($rect->x).','.number($rect->y).','.number($rect->width).','.number($rect->height);
}

function number(float $value): string
{
    return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
}
