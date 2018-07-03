<?php
declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Factory\RepositoryFactory;
use BetaKiller\Model\AbstractEntityInterface;

class EntityManager
{
    /**
     * @var \BetaKiller\Factory\RepositoryFactory
     */
    private $repositoryFactory;

    /**
     * EntityManager constructor.
     *
     * @param \BetaKiller\Factory\RepositoryFactory $repositoryFactory
     */
    public function __construct(RepositoryFactory $repositoryFactory)
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
        $repository = $this->repositoryFactory->create($entity->getModelName());
        $repository->save($entity);
    }
}
