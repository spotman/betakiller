<?php

declare(strict_types=1);

namespace BetaKiller\Query;

/**
 * Marker interface for Repository` Query
 */
interface RepositoryQueryInterface
{
    public function limit(int $value): RepositoryQueryInterface;

    public function hasLimit(): bool;

    public function getLimit(): int;
}
