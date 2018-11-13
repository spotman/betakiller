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
        $entity       = $this->getEntity($arguments);
        $responseData = $this->create($entity, $arguments, $user);

        $this->saveEntity($entity);

        return $this->response($responseData);
    }

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $model
     * @param \Spotman\Defence\ArgumentsInterface       $arguments
     *
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @return mixed
     */
    abstract protected function create($model, ArgumentsInterface $arguments, UserInterface $user);
}
