<?php

declare(strict_types=1);

namespace Atelier\Layout\Exception;

/**
 * Exception thrown when invalid arguments are passed to layout methods.
 *
 * Indicates that a method or constructor received arguments that don't meet
 * the expected criteria, such as negative lengths, out-of-range indices, or
 * malformed geometry inputs.
 */
final class InvalidArgumentException extends \InvalidArgumentException implements LayoutExceptionInterface
{
}
