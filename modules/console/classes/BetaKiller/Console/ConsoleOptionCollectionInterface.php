<?php

declare(strict_types=1);

namespace BetaKiller\Console;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @method ConsoleOptionInterface[] getIterator()
 */
interface ConsoleOptionCollectionInterface extends IteratorAggregate
{
    public function has(string $name): bool;

    public function get(string $name): ConsoleOptionInterface;
}
