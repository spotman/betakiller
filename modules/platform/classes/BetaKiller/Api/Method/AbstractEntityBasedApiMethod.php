<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Repository\RepositoryInterface;
use Spotman\Api\ArgumentsInterface;
use Spotman\Api\Method\AbstractApiMethod;

abstract class AbstractEntityBasedApiMethod extends AbstractApiMethod implements EntityBasedApiMethodInterface
{
    /**
     * @Inject
     * @var \BetaKiller\Factory\RepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @var \BetaKiller\Repository\RepositoryInterface
     */
    private $repository;

    /**
     * @param \Spotman\Api\ArgumentsInterface $arguments
     *
     * @return \BetaKiller\Model\AbstractEntityInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getEntity(ArgumentsInterface $arguments): AbstractEntityInterface
    {
        return $arguments->hasID()
            ? $this->fetchEntity($arguments->getID())
            : $this->createEntity();
    }

    /**
     * Returns new entity
     *
     * @return \BetaKiller\Model\AbstractEntityInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function createEntity(): AbstractEntityInterface
    {
        return $this->getRepository()->create();
    }

    /**
     * @return \BetaKiller\Repository\RepositoryInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function getRepository(): RepositoryInterface
    {
        if (!$this->repository) {
            $this->repository = $this->fetchRepository();
        }

        return $this->repository;
    }

    /**
     * @return \BetaKiller\Repository\RepositoryInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function fetchRepository(): RepositoryInterface
    {
        // Repository name is equal to API collection name
        $repoName = $this->getCollectionName();

        return $this->repositoryFactory->create($repoName);
    }

    /**
     * @param string $id
     *
     * @return \BetaKiller\Model\AbstractEntityInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function fetchEntity(string $id): AbstractEntityInterface
    {
        return $this->getRepository()->findById($id);
    }

    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function saveEntity(AbstractEntityInterface $entity): void
    {
        $this->getRepository()->save($entity);
    }

    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function deleteEntity(AbstractEntityInterface $entity): void
    {
        $this->getRepository()->delete($entity);
    }
}
