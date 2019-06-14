<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;

abstract class AbstractEntityReadApiMethod extends AbstractEntityBasedApiMethod
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
        $entity = $this->getEntity($arguments);

        return $this->read($entity, $user, $arguments);
    }

    /**
     * Implement this method
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $model
     * @param \BetaKiller\Model\UserInterface           $user
     * @param \Spotman\Defence\ArgumentsInterface       $arguments
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    abstract protected function read(
        AbstractEntityInterface $model,
        UserInterface $user,
        ArgumentsInterface $arguments
    ): ?ApiMethodResponse;
}
