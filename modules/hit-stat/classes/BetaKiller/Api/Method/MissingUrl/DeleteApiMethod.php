<?php
namespace BetaKiller\Api\Method\MissingUrl;

use BetaKiller\Api\Method\AbstractEntityDeleteApiMethod;
use Spotman\Defence\DefinitionBuilderInterface;

class DeleteApiMethod extends AbstractEntityDeleteApiMethod
{
    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
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
