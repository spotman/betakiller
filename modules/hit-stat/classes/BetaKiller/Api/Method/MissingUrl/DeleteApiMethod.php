<?php
namespace BetaKiller\Api\Method\MissingUrl;

use BetaKiller\Api\Method\AbstractEntityDeleteApiMethod;
use Spotman\Defence\DefinitionBuilderInterface;

class DeleteApiMethod extends AbstractEntityDeleteApiMethod
{
    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition()
            ->identity();
    }

    /**
     * Implement this method
     *
     * @param                                                 $model
     *
     * @param \BetaKiller\Api\Method\MissingUrl\UserInterface $user
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function delete($model, UserInterface $user): void
    {
        $this->deleteEntity($model);
    }
}
