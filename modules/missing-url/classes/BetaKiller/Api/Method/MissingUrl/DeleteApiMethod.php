<?php
namespace BetaKiller\Api\Method\MissingUrl;

use BetaKiller\Api\Method\AbstractEntityDeleteApiMethod;
use Spotman\Defence\ArgumentsDefinitionInterface;

class DeleteApiMethod extends AbstractEntityDeleteApiMethod
{
    /**
     * @return \Spotman\Defence\ArgumentsDefinitionInterface
     */
    public function getArgumentsDefinition(): ArgumentsDefinitionInterface
    {
        return $this->definition()
            ->identity();
    }

    /**
     * Implement this method
     *
     * @param $model
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function delete($model): void
    {
        $this->deleteEntity($model);
    }
}
