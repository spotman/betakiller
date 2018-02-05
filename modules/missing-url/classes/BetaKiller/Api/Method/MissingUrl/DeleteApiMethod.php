<?php
namespace BetaKiller\Api\Method\MissingUrl;

use BetaKiller\Api\Method\AbstractEntityDeleteApiMethod;

class DeleteApiMethod extends AbstractEntityDeleteApiMethod
{
    /**
     * Implement this method
     *
     * @param $model
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function delete($model)
    {
        $this->deleteEntity();
    }
}
