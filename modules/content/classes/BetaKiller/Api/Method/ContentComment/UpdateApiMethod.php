<?php
namespace BetaKiller\Api\Method\ContentComment;

use Spotman\Api\Method\AbstractModelUpdateApiApiMethod;

class UpdateApiMethod extends AbstractModelUpdateApiApiMethod
{
    use ContentCommentMethodTrait;

    /**
     * Override this method
     *
     * @param \Model_ContentComment $model
     * @param                       $data
     *
     * @throws \Spotman\Api\ApiMethodException
     * @return \Spotman\Api\AbstractCrudMethodsModelInterface|null
     */
    protected function update($model, $data)
    {
        if (isset($data->author_name)) {
            $model->set_guest_author_name($this->sanitize_string($data->author_name));
        }

        if (isset($data->message)) {
            $model->set_message($data->message);
        }

        $model->update();

        // Return updated model data
        return $model;
    }
}
