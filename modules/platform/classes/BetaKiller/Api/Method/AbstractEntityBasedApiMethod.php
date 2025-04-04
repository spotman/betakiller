<?php

namespace BetaKiller\Api\Method;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Repository\RepositoryInterface;
use Spotman\Api\ApiMethodException;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;

abstract readonly class AbstractEntityBasedApiMethod extends AbstractApiMethod implements EntityBasedApiMethodInterface
{
    /**
     * @param \BetaKiller\Api\Method\EntityBasedApiMethodHelper $helper
     */
    public function __construct(private EntityBasedApiMethodHelper $helper)
    {
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     *
     * @return \BetaKiller\Model\AbstractEntityInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    protected function getEntity(ArgumentsInterface $arguments): AbstractEntityInterface
    {
        return $this->helper->getEntity($this, $arguments);
    }

    /**
     * @return \BetaKiller\Repository\RepositoryInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function getRepository(): RepositoryInterface
    {
        static $repository;

        if (!$repository) {
            $repository = $this->fetchRepository();
        }

        return $repository;
    }

    /**
     * @return \BetaKiller\Repository\RepositoryInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function fetchRepository(): RepositoryInterface
    {
        return $this->helper->getRepository($this);
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

    /**
     * @param string $entityName
     * @param string $value
     *
     * @return string
     */
    protected function decodeIdentity(string $entityName, string $value): string
    {
        return $this->helper->decodeIdentity($entityName, $value);
    }

    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @return string
     */
    protected function encodeIdentity(AbstractEntityInterface $entity): string
    {
        return $this->helper->encodeIdentity($entity);
    }
}
