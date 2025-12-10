<?php

declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Query\RepositoryQueryInterface;
use BetaKiller\Search\SearchResultsInterface;

interface HasSearchQueryInterface extends RepositoryInterface
{
    public function search(RepositoryQueryInterface $query, int $page, int $itemsPerPage = null): SearchResultsInterface;

    public function count(RepositoryQueryInterface $query): int;
}
