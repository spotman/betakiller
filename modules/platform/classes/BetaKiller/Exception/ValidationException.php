<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

use ArrayIterator;
use BetaKiller\Exception;
use Iterator;
use IteratorAggregate;

class ValidationException extends Exception implements IteratorAggregate, \JsonSerializable
{
    /**
     * @var \BetaKiller\Exception\ValidationExceptionItem[]
     */
    private $items = [];

    /**
     * ValidationException constructor.
     *
     * @param \Throwable|null $previous
     */
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct('Validation exception, iterate it to get more info', null, 0, $previous);
    }

    /**
     * Retrieve an external iterator
     *
     * @return \Iterator|\BetaKiller\Exception\ValidationExceptionItem[]
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->items);
    }

    public function add(string $field, string $message): void
    {
        if (isset($this->items[$field])) {
            throw new Exception('Duplicate field ":name"', [':name' => $field]);
        }

        $this->items[$field] = new ValidationExceptionItem($field, $message);
    }

    public function getFor(string $field): ValidationExceptionItem
    {
        if (!isset($this->items[$field])) {
            throw new Exception('Missing field ":name"', [':name' => $field]);
        }

        return $this->items[$field];
    }

    public function getFirstItem(): ValidationExceptionItem
    {
        return reset($this->items);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return \array_map(function(ValidationExceptionItem $item) {
            return $item->getMessage();
        }, $this->items);
    }
}
