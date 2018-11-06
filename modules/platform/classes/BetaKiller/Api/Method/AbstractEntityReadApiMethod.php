<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\ArgumentsInterface;

abstract class AbstractEntityReadApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * @param \Spotman\Api\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $entity       = $this->getEntity($arguments);
        $responseData = $this->read($entity);

        return $this->response($responseData);
    }

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $model
     *
     * @return mixed
     */
    abstract protected function read($model);
}
