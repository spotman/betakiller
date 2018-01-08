<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractEntityUpdateApiApiMethod;

class UpdateApiMethod extends AbstractEntityUpdateApiApiMethod
{
    use ContentCommentMethodTrait;

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\ContentComment $model
     * @param                       $data
     *
     * @throws \Spotman\Api\ApiMethodException
     * @return \BetaKiller\Model\AbstractEntityInterface|null
     */
    protected function update($model, $data)
    {
        if (isset($data->author_name)) {
            $model->setGuestAuthorName($this->sanitizeString($data->author_name));
        }

        if (isset($data->message)) {
            $model->setMessage($data->message);
        }

        $model->update();

        // Return updated model data
        return $model;
    }
}
