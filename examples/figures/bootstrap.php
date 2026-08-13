<?php

declare(strict_types=1);

/*
 * Dev-only autoloader for the figure generator: atelier/layout from this
 * package's source, atelier/svg from the sibling package. Keeps the generator
 * runnable with a plain `php examples/illustrations.php`, no composer install.
 */

spl_autoload_register(static function (string $class): void {
    $map = [
        'Atelier\\Layout\\' => __DIR__.'/../../src',
        'Atelier\\Svg\\' => __DIR__.'/../../../svg/src',
    ];
    foreach ($map as $prefix => $root) {
        if (\str_starts_with($class, $prefix)) {
            $path = $root.'/'.\str_replace('\\', '/', \substr($class, \strlen($prefix))).'.php';
            if (\is_file($path)) {
                require $path;
            }

            return;
        }
    }
});
