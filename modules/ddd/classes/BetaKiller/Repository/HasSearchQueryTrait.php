<?php

declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\AbstractEntityInterface;
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

        if ($query->hasLimit()) {
            $itemsPerPage = $query->getLimit();
        }

        return $this->findAllResults($orm, $page, $itemsPerPage, $this->hasReverseSearchResults());
    }

    /**
     * @param \BetaKiller\Query\RepositoryQueryInterface $query
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|null|mixed
     */
    public function find(RepositoryQueryInterface $query): mixed
    {
        $orm = $this->getOrmInstance();

        $this->applySearchQuery($orm, $query);

        return $this->findOne($orm);
    }

    public function count(RepositoryQueryInterface $query): int
    {
        $orm = $this->getOrmInstance();

        $this->applySearchQuery($orm, $query);

        return $this->countSelf($orm);
    }

    abstract protected function applySearchQuery(ExtendedOrmInterface $orm, RepositoryQueryInterface $query): void;

    abstract protected function getDefaultItemsPerPage(): int;

    abstract protected function hasReverseSearchResults(): bool;
}
