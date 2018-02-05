<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Repository\RepositoryInterface;
use Spotman\Api\Method\AbstractApiMethod;
use BetaKiller\Model\AbstractEntityInterface;

abstract class AbstractEntityBasedApiMethod extends AbstractApiMethod implements EntityBasedApiMethodInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \BetaKiller\Model\AbstractEntityInterface
     */
    private $entity;

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
     * Returns new entity
     *
     * @return \BetaKiller\Model\AbstractEntityInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function createEntity(): AbstractEntityInterface
    {
        return $this->getRepository()->create();
    }

    /**
     * @return \BetaKiller\Repository\RepositoryInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function getRepository(): RepositoryInterface
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
     * @param $id
     *
     * @return \BetaKiller\Model\AbstractEntityInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function fetchEntity(int $id): AbstractEntityInterface
    {
        return $this->getRepository()->findById($id);
    }

    /**
     * @return \BetaKiller\Model\AbstractEntityInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getEntity(): AbstractEntityInterface
    {
        if (!$this->entity) {
            $this->entity = $this->id
                ? $this->fetchEntity($this->id)
                : $this->createEntity();
        }

        return $this->entity;
    }

    /**
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function saveEntity(): void
    {
        $this->getRepository()->save($this->entity);
    }

    /**
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function deleteEntity(): void
    {
        $this->getRepository()->delete($this->entity);
    }
}
