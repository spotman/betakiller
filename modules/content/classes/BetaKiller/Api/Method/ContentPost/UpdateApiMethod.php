<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityUpdateApiMethod;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\ContentPostInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

final class UpdateApiMethod extends AbstractEntityUpdateApiMethod
{
    private const ARG_DATA    = 'data';
    private const ARG_LABEL   = 'label';
    private const ARG_URI     = 'uri';
    private const ARG_TITLE   = 'title';
    private const ARG_DESC    = 'description';
    private const ARG_CONTENT = 'content';

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->identity()
            ->compositeStart(self::ARG_DATA)
            //
            ->string(self::ARG_LABEL)->optional()
            ->string(self::ARG_URI)->optional()
            ->string(self::ARG_TITLE)->optional()
            ->string(self::ARG_DESC)->optional()
            ->string(self::ARG_CONTENT)->optional()
            //
            ->compositeEnd();
    }

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     * @param \Spotman\Defence\ArgumentsInterface       $arguments
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function processUpdate(AbstractEntityInterface $entity, ArgumentsInterface $arguments, UserInterface $user): ?AbstractEntityInterface
    {
        if (!$entity instanceof ContentPostInterface) {
            throw new \LogicException;
        }

        $data = $arguments->getArray(self::ARG_DATA);

        if (isset($data[self::ARG_LABEL])) {
            $entity->setLabel($data[self::ARG_LABEL]);
        }

        if (isset($data[self::ARG_URI]) && $data[self::ARG_URI] !== $entity->getUri()) {
            // TODO deal with url change
            $entity->setUri($data[self::ARG_URI]);
        }

        if (isset($data[self::ARG_TITLE])) {
            $entity->setTitle($data[self::ARG_TITLE]);
        }

        if (isset($data[self::ARG_DESC])) {
            $entity->setDescription($data[self::ARG_DESC]);
        }

        if (isset($data[self::ARG_CONTENT])) {
            $entity->setContent($data[self::ARG_CONTENT]);
        }

        $entity->injectNewRevisionAuthor($user);

        $this->saveEntity($entity);

        return $entity;
    }
}
