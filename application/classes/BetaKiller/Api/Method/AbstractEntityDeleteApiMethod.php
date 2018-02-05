<?php
namespace BetaKiller\Api\Method;

use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;

abstract class AbstractEntityDeleteApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * AbstractEntityDeleteApiMethod constructor.
     *
     * @param $id
     *
     * @throws \Spotman\Api\ApiMethodException
     */
    public function __construct($id)
    {
        $this->id = (int)$id;

        if (!$this->id) {
            throw new ApiMethodException('Can not delete entity with empty id');
        }
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Spotman\Api\ApiMethodException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function execute(): ?ApiMethodResponse
    {
        $model = $this->getEntity();

        $this->delete($model);

        return $this->response(true);
    }

    /**
     * Implement this method
     *
     * @param $model
     *
     * @throws \Spotman\Api\ApiMethodException
     */
    abstract protected function delete($model);
}
