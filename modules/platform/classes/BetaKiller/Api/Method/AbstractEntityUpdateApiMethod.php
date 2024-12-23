<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;

abstract readonly class AbstractEntityUpdateApiMethod extends AbstractEntityBasedApiMethod
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
        if (!$arguments->hasID()) {
            throw new ApiMethodException('Can not update entity with empty id');
        }

        $model    = $this->getEntity($arguments);
        $response = $this->processUpdate($model, $arguments, $user);

        return $this->response($response);
    }

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $model
     * @param \Spotman\Defence\ArgumentsInterface       $arguments
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|null
     */
    abstract protected function processUpdate(
        AbstractEntityInterface $entity,
        ArgumentsInterface $arguments,
        UserInterface $user
    ): ?AbstractEntityInterface;
}
