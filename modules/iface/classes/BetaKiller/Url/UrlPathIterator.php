<?php
namespace BetaKiller\Url;

class UrlPathIterator extends \ArrayIterator
{
    /**
     * @var array
     */
    private $array;

    public function __construct(string $path)
    {
        $path  = trim($path, '/ ');
        $parts = $path ? explode('/', $path) : [];

        parent::__construct($parts);
    }

    /**
     *
     * @throws \OutOfRangeException
     */
    public function prev(): void
    {
        $prev = $this->key() - 1;

        if ($prev < 0 || $prev > ($this->count() - 1)) {
            throw new \OutOfRangeException('Can not seek to previous array element');
        }

        $this->seek($prev);
    }

    public function rootRequested(): bool
    {
        return !$this->count();
    }
}
