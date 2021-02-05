<?php
declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Factory\RepositoryFactoryInterface;
use BetaKiller\Model\AbstractEntityInterface;

class EntityManager
{
    /**
     * @var \BetaKiller\Factory\RepositoryFactoryInterface
     */
    private RepositoryFactoryInterface $repositoryFactory;

    /**
     * EntityManager constructor.
     *
     * @param \BetaKiller\Factory\RepositoryFactoryInterface $repositoryFactory
     */
    public function __construct(RepositoryFactoryInterface $repositoryFactory)
    {
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function persist(AbstractEntityInterface $entity): void
    {
        $repo = $this->repositoryFactory->create($entity::getModelName());
        $repo->save($entity);
    }
}
