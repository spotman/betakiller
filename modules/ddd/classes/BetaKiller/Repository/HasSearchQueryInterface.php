<?php

declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Query\RepositoryQueryInterface;
use BetaKiller\Search\SearchResultsInterface;

interface HasSearchQueryInterface extends RepositoryInterface
{
    public function search(RepositoryQueryInterface $query, int $page, int $itemsPerPage = null): SearchResultsInterface;

    /**
     * @param \BetaKiller\Query\RepositoryQueryInterface $query
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|null|mixed
     */
    public function find(RepositoryQueryInterface $query): mixed;

    public function count(RepositoryQueryInterface $query): int;
}
