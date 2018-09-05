<?php
namespace BetaKiller\Api\Method;

use Spotman\Api\ApiMethodResponse;

abstract class AbstractEntityReadApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function execute(): ?ApiMethodResponse
    {
        $entity       = $this->getEntity();
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
