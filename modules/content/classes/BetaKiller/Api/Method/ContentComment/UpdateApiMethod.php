<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractEntityUpdateApiMethod;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

final class UpdateApiMethod extends AbstractEntityUpdateApiMethod
{
    private const ARG_DATA        = 'data';
    private const ARG_AUTHOR_NAME = 'author_name';
    private const ARG_MESSAGE     = 'message';

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->compositeStart(self::ARG_DATA)
            ->string(self::ARG_AUTHOR_NAME)->optional()
            ->string(self::ARG_MESSAGE)->optional();
    }

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $model
     * @param \Spotman\Defence\ArgumentsInterface       $arguments
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function processUpdate(AbstractEntityInterface $model, ArgumentsInterface $arguments, UserInterface $user): ?AbstractEntityInterface
    {
        if (!$model instanceof ContentCommentInterface) {
            throw new \LogicException;
        }

        $data = $arguments->getArray(self::ARG_DATA);

        if (isset($data[self::ARG_AUTHOR_NAME])) {
            $model->setGuestAuthorName($data[self::ARG_AUTHOR_NAME]);
        }

        if (isset($data[self::ARG_MESSAGE])) {
            $model->setMessage($data[self::ARG_MESSAGE]);
        }

        $this->saveEntity($model);

        // Return updated model data
        return $model;
    }
}
