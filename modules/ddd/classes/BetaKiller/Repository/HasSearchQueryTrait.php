<?php

declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Query\RepositoryQueryInterface;
use BetaKiller\Search\SearchResultsInterface;

trait HasSearchQueryTrait
{
    public function search(RepositoryQueryInterface $query, int $page, int $itemsPerPage = null): SearchResultsInterface
    {
        $itemsPerPage ??= $this->getDefaultItemsPerPage();

        $orm = $this->getOrmInstance();

        $this->applySearchQuery($orm, $query);

        return $this->findAllResults($orm, $page, $itemsPerPage, $this->hasReverseSearchResults());
    }

    abstract protected function applySearchQuery(ExtendedOrmInterface $orm, RepositoryQueryInterface $query): void;

    abstract protected function getDefaultItemsPerPage(): int;

    abstract protected function hasReverseSearchResults(): bool;
}
