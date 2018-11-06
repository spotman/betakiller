<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\ArgumentsInterface;

abstract class AbstractEntityDeleteApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * @param \Spotman\Api\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        if (!$arguments->hasID()) {
            throw new ApiMethodException('Can not delete entity with empty id');
        }

        $model = $this->getEntity($arguments);

        $this->delete($model);

        return $this->response(true);
    }

    /**
     * Implement this method
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $model
     *
     * @throws \Spotman\Api\ApiMethodException
     */
    abstract protected function delete($model): void;
}
