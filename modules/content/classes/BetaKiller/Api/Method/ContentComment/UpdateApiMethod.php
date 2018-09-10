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
     * @param                                  $data
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|null
     * @throws \Kohana_Exception
     */
    protected function update($model, $data): \BetaKiller\Model\AbstractEntityInterface
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
