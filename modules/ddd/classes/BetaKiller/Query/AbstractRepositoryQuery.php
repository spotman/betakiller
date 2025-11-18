<?php

declare(strict_types=1);

namespace BetaKiller\Query;

/**
 * Base implementation for Repository` Query
 */
abstract class AbstractRepositoryQuery implements RepositoryQueryInterface
{
    private ?int $limit = null;

    public static function create(): static
    {
        return new static();
    }

    protected function __construct()
    {
    }

    public function limit(int $value): AbstractRepositoryQuery
    {
        assert($value > 0, 'Query limit must be positive');

        $this->limit = $value;

        return $this;
    }

    public function hasLimit(): bool
    {
        return $this->limit !== null;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
