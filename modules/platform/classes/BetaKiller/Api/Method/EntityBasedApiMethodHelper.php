<?php

declare(strict_types=1);

namespace BetaKiller\Api\Method;

use BetaKiller\Factory\RepositoryFactoryInterface;
use BetaKiller\IdentityConverterInterface;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Repository\RepositoryInterface;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodInterface;
use Spotman\Api\EntityDetectorInterface;
use Spotman\Defence\ArgumentsInterface;

final readonly class EntityBasedApiMethodHelper implements EntityDetectorInterface
{
    public function __construct(private RepositoryFactoryInterface $repositoryFactory, private IdentityConverterInterface $converter)
    {
    }

    /**
     * @param \BetaKiller\Api\Method\EntityBasedApiMethodInterface $method
     * @param \Spotman\Defence\ArgumentsInterface                  $arguments
     *
     * @return \BetaKiller\Model\AbstractEntityInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    public function getEntity(ApiMethodInterface $method, ArgumentsInterface $arguments): AbstractEntityInterface
    {
        if (!$arguments->hasID()) {
            throw new ApiMethodException('Missing identity is required for entity processing');
        }

        // Entity name is equal to API collection name
        $entityName = $method::getCollectionName();

        $id = $this->decodeIdentity($entityName, $arguments->getID());

        return $this->getRepository($method)->getById($id);
    }

    /**
     * @param \Spotman\Api\ApiMethodInterface $method
     *
     * @return \BetaKiller\Repository\RepositoryInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getRepository(ApiMethodInterface $method): RepositoryInterface
    {
        // Repository name is equal to API collection name
        $repoName = $method::getCollectionName();

        return $this->repositoryFactory->create($repoName);
    }

    /**
     * @param \Spotman\Api\ApiMethodInterface           $method
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function saveEntity(ApiMethodInterface $method, AbstractEntityInterface $entity): void
    {
        $this->getRepository($method)->save($entity);
    }

    /**
     * @param \Spotman\Api\ApiMethodInterface           $method
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function deleteEntity(ApiMethodInterface $method, AbstractEntityInterface $entity): void
    {
        $this->getRepository($method)->delete($entity);
    }

    public function decodeIdentity(string $entityName, string $value): string
    {
        return $this->converter->decode($entityName, $value);
    }

    public function encodeIdentity(AbstractEntityInterface $entity): string
    {
        return $this->converter->encode($entity);
    }
}
