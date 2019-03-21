<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractEntityCreateApiMethod;
use BetaKiller\Model\ContentComment;
use BetaKiller\Model\ContentCommentInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class CreateApiMethod extends AbstractEntityCreateApiMethod
{
    private const ARG_DATA        = 'data';
    private const ARG_AUTHOR_NAME = 'author_name';
    private const ARG_MESSAGE     = 'message';

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition()
            ->composite(self::ARG_DATA)
            ->string(self::ARG_AUTHOR_NAME)
            ->string(self::ARG_MESSAGE);
    }

    /**
     * Implement this method
     *
     * @param \Spotman\Defence\ArgumentsInterface       $arguments
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @return \BetaKiller\Model\ContentCommentInterface
     */
    protected function create(ArgumentsInterface $arguments, UserInterface $user)
    {
        $model = new ContentComment;

        $data = $arguments->getArray(self::ARG_DATA);

        $model->setGuestAuthorName($data[self::ARG_AUTHOR_NAME]);
        $model->setMessage($data[self::ARG_MESSAGE]);

        // Return created model data
        return $model;
    }
}
