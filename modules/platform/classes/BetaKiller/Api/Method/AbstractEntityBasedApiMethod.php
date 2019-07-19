<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Repository\RepositoryInterface;
use Spotman\Api\ApiMethodException;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;

abstract class AbstractEntityBasedApiMethod extends AbstractApiMethod implements EntityBasedApiMethodInterface
{
    /**
     * @Inject
     * @var \BetaKiller\Factory\RepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @Inject
     * @var \BetaKiller\IdentityConverterInterface
     */
    private $converter;

    /**
     * @var \BetaKiller\Repository\RepositoryInterface
     */
    private $repository;


    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     *
     * @return \BetaKiller\Model\AbstractEntityInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getEntity(ArgumentsInterface $arguments): AbstractEntityInterface
    {
        if (!$arguments->hasID()) {
            throw new ApiMethodException('Missing identity is required for entity processing');
        }

        // Entity name is equal to API collection name
        $entityName = $this->getCollectionName();

        $id = $this->decodeIdentity($entityName, $arguments->getID());

        return $this->getRepository()->findById($id);
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

    protected function decodeIdentity(string $entityName, string $value): string
    {
        return $this->converter->decode($entityName, $value);
    }

    protected function encodeIdentity(AbstractEntityInterface $entity): string
    {
        return $this->converter->encode($entity);
    }
}
