<?php
namespace BetaKiller\Repository;

abstract class AbstractPredefinedRepository extends AbstractReadOnlyRepository
{
    /**
     * Creates empty entity
     *
     * @return mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function create()
    {
        throw new RepositoryException(':repo repository can not create new entity', [
            ':repo' => static::getCodename(),
        ]);
    }
}
