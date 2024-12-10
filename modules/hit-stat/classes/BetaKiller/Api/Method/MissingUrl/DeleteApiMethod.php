<?php
namespace BetaKiller\Api\Method\MissingUrl;

use BetaKiller\Api\Method\AbstractEntityDeleteApiMethod;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Defence\DefinitionBuilderInterface;

final readonly class DeleteApiMethod extends AbstractEntityDeleteApiMethod
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
     * @param \BetaKiller\Model\AbstractEntityInterface       $entity
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function processDelete(AbstractEntityInterface $entity, UserInterface $user): void
    {
        $this->deleteEntity($entity);
    }
}
