<?php
namespace BetaKiller\Repository;


abstract class AbstractReadOnlyRepository extends AbstractRepository
{
    /**
     * @param $entity
     */
    public function save($entity): void
    {
        throw new RepositoryException(':repo is read-only repository', [
            ':repo' => static::getCodename(),
        ]);
    }

    /**
     * @param $entity
     */
    public function delete($entity): void
    {
        throw new RepositoryException(':repo is read-only repository', [
            ':repo' => static::getCodename(),
        ]);
    }
}
