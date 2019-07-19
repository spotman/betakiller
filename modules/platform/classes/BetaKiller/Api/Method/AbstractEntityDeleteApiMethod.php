<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;

abstract class AbstractEntityDeleteApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
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

        $entity = $this->getEntity($arguments);

        $this->delete($entity, $user);

        return $this->response(true);
    }

    /**
     * Implement this method
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @throws \Spotman\Api\ApiMethodException
     */
    abstract protected function delete($entity, UserInterface $user): void;
}
