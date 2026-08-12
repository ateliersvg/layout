<?php

declare(strict_types=1);

/*
 * Generates the layout documentation illustrations.
 *
 * Geometry is computed by atelier/layout; rendering by atelier/svg -- the
 * canonical demo of the stack. The framework (Drawable shapes, helpers, shared
 * stylesheet, renderer) lives in figures/figures.php; the figures themselves are
 * declared per family under figures/scenes/. Output: docs/images/<id>.svg.
 *
 * Run: php examples/illustrations.php
 */

namespace Atelier\Layout\Examples\Figures;

require __DIR__.'/figures/bootstrap.php';
require __DIR__.'/figures/figures.php';

$registry = [
    ...require __DIR__.'/figures/scenes/composition.php',
    ...require __DIR__.'/figures/scenes/geometry.php',
    ...require __DIR__.'/figures/scenes/helpers.php',
    ...require __DIR__.'/figures/scenes/connectors.php',
];

renderAll($registry, __DIR__.'/../docs/images');
