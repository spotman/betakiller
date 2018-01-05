<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractEntityCreateApiMethod;

class CreateApiMethod extends AbstractEntityCreateApiMethod
{
    use ContentCommentMethodTrait;

    /**
     * Implement this method
     *
     * @param \BetaKiller\Api\Method\ContentComment\AbstractEntityInterface $model
     * @param                                                               $data
     *
     * @return \BetaKiller\Api\Method\ContentComment\AbstractEntityInterface
     */
    protected function create($model, $data)
    {
        $model->set_guest_author_name($this->sanitizeString($data->author_name));
        $model->set_message($data->message);

        $model->create();

        // Return created model data
        return $model;
    }
}
