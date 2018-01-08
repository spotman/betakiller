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
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     * @return \BetaKiller\Model\AbstractEntityInterface|mixed|null
     */
    protected function update($model, $data)
    {
        if (isset($data->label)) {
            $model->setLabel($this->sanitizeString($data->label));
        }

        // TODO deal with url change
//        if (isset($data->uri)) {
//            $model->setUri($this->sanitizeString($data->uri));
//        }

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
