<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityUpdateApiApiMethod;

class UpdateApiMethod extends AbstractEntityUpdateApiApiMethod
{
    use ContentPostMethodTrait;

    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\ContentPostInterface $model
     * @param                                        $data
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|mixed|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function update($model, $data): \BetaKiller\Model\AbstractEntityInterface
    {
        if (isset($data->label)) {
            $model->setLabel($this->sanitizeString($data->label));
        }

        if (isset($data->uri) && $data->uri !== $model->getUri()) {
            // TODO deal with url change
            $model->setUri($this->sanitizeString($data->uri));
        }

        if (isset($data->title)) {
            $model->setTitle($this->sanitizeString($data->title));
        }

        if (isset($data->description)) {
            $model->setDescription($this->sanitizeString($data->description));
        }

        if (isset($data->content)) {
            $model->setContent($data->content);
        }

        $model->injectNewRevisionAuthor($this->user);

        $this->saveEntity();

        return true;
    }
}
