<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;

abstract class AbstractEntityCreateApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $entity = $this->create($arguments, $user);

        $this->saveEntity($entity);

        return $this->makeResponse($entity, $user);
    }

    /**
     * Implement this method
     *
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     *
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|object
     */
    abstract protected function create(ArgumentsInterface $arguments, UserInterface $user);

    protected function makeResponse($entity, UserInterface $user): ApiMethodResponse
    {
        return $this->response($entity);
    }
}
