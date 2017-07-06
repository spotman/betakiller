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
     */
    protected function createEntity(): AbstractEntityInterface
    {
        return $this->getRepository()->create();
    }

    protected function getRepository(): RepositoryInterface
    {
        if (!$this->repository) {
            $this->repository = $this->fetchRepository();
        }

        return $this->repository;
    }

    private function fetchRepository(): RepositoryInterface
    {
        // Repository name is equal to API collection name
        $repoName = $this->getCollectionName();

        return $this->repositoryFactory->create($repoName);
    }

    private function fetchEntity($id): AbstractEntityInterface
    {
        return $this->getRepository()->findById($id);
    }

    /**
     * @return \BetaKiller\Model\AbstractEntityInterface
     */
    public function getEntity()
    {
        if (!$this->entity) {
            $this->entity = $this->fetchEntity($this->id);
        }

        return $this->entity;
    }
}
