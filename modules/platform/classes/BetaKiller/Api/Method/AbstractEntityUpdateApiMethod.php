<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;

abstract class AbstractEntityUpdateApiMethod extends AbstractEntityBasedApiMethod
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
        $response = $this->update($model, $arguments, $user);

        return $this->response($response);
    }

    /**
     * Override this method
     *
     * @param                                 $model
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|null
     */
    abstract protected function update(
        $model,
        ArgumentsInterface $arguments,
        UserInterface $user
    ): ?AbstractEntityInterface;
}
