<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityUpdateApiMethod;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Defence\DefinitionBuilderInterface;
use Spotman\Defence\ArgumentsInterface;

class UpdateApiMethod extends AbstractEntityUpdateApiMethod
{
    use ContentPostMethodTrait;

    private const ARG_DATA = 'data';

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition()
            ->array(self::ARG_DATA);
    }

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\ContentPostInterface $model
     * @param \Spotman\Defence\ArgumentsInterface    $arguments
     * @param \BetaKiller\Model\UserInterface        $user
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|mixed|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function update($model, ArgumentsInterface $arguments, UserInterface $user): ?AbstractEntityInterface
    {
        $data = $arguments->getArray(self::ARG_DATA);

        if (isset($data['label'])) {
            $model->setLabel($this->sanitizeString($data['label']));
        }

        if (isset($data['uri']) && $data['uri'] !== $model->getUri()) {
            // TODO deal with url change
            $model->setUri($this->sanitizeString($data['uri']));
        }

        if (isset($data['title'])) {
            $model->setTitle($this->sanitizeString($data['title']));
        }

        if (isset($data['description'])) {
            $model->setDescription($this->sanitizeString($data['description']));
        }

        if (isset($data['content'])) {
            $model->setContent($data['content']);
        }

        $model->injectNewRevisionAuthor($user);

        $this->saveEntity($model);

        return true;
    }
}
