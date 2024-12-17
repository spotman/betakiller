<?php

declare(strict_types=1);

namespace BetaKiller\Console;

use ArrayIterator;
use LogicException;
use Traversable;
use Webmozart\Assert\Assert;

readonly class ConsoleOptionCollection implements ConsoleOptionCollectionInterface
{
    private array $items;

    /**
     * @param \BetaKiller\Console\ConsoleOptionInterface[] $items
     */
    public function __construct(array $items)
    {
        Assert::allIsInstanceOf($items, ConsoleOptionInterface::class);

        $namedItems = [];

        foreach ($items as $item) {
            $name = $item->getName();

            if (isset($namedItems[$name])) {
                throw new LogicException(sprintf('Duplicate option "%s"', $name));
            }

            $namedItems[$name] = $item;
        }

        $this->items = $namedItems;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function has(string $name): bool
    {
        return isset($this->items[$name]);
    }

    public function get(string $name): ConsoleOptionInterface
    {
        $option = $this->items[$name] ?? null;

        if (!$option) {
            throw new LogicException(sprintf('Unknown console option "%s"', $name));
        }

        return $option;
    }
}
