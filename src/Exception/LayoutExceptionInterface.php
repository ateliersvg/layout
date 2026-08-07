<?php

declare(strict_types=1);

namespace Atelier\Layout\Exception;

/**
 * Marker interface for all exceptions thrown by the Atelier Layout library.
 *
 * All library-specific exceptions implement this interface, allowing
 * callers to catch any library exception with a single catch block:
 *
 * ```php
 * try {
 *     $connection = (new OrthogonalConnector())->connect($from, $to);
 * } catch (LayoutExceptionInterface $e) {
 *     // Handle any layout exception
 * }
 * ```
 */
interface LayoutExceptionInterface extends \Throwable
{
}
