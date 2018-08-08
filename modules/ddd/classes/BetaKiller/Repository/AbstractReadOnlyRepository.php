<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

abstract class AbstractReadOnlyRepository extends AbstractRepository
{
    /**
     * @param $entity
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function save($entity): void
    {
        throw new RepositoryException(':repo is read-only repository', [
            ':repo' => static::getCodename(),
        ]);
    }

    /**
     * @param $entity
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function delete($entity): void
    {
        throw new RepositoryException(':repo is read-only repository', [
            ':repo' => static::getCodename(),
        ]);
    }
}
