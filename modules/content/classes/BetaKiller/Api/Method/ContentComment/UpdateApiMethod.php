<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractEntityUpdateApiMethod;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Api\ArgumentsDefinitionInterface;
use Spotman\Api\ArgumentsInterface;

class UpdateApiMethod extends AbstractEntityUpdateApiMethod
{
    use ContentCommentMethodTrait;

    private const ARG_DATA = 'data';

    /**
     * @return \Spotman\Api\ArgumentsDefinitionInterface
     */
    public function getArgumentsDefinition(): ArgumentsDefinitionInterface
    {
        return $this->definition()
            ->array(self::ARG_DATA);
    }

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\ContentCommentInterface $model
     * @param \Spotman\Api\ArgumentsInterface           $arguments
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function update($model, ArgumentsInterface $arguments, UserInterface $user): ?AbstractEntityInterface
    {
        $data = $arguments->getArray(self::ARG_DATA);

        if (isset($data['author_name'])) {
            $model->setGuestAuthorName($this->sanitizeString($data['author_name']));
        }

        if (isset($data['message'])) {
            $model->setMessage($data['message']);
        }

        $this->saveEntity($model);

        // Return updated model data
        return $model;
    }
}
