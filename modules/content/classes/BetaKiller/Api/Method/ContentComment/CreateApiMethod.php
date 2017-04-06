<?php
namespace BetaKiller\Api\Method\ContentComment;

use Spotman\Api\Method\AbstractModelCreateApiMethod;

class CreateApiMethod extends AbstractModelCreateApiMethod
{
    use ContentCommentMethodTrait;

    /**
     * Implement this method
     *
     * @param \Model_ContentComment $model
     * @param                       $data
     *
     * @throws \Spotman\Api\ApiMethodException
     * @return \Spotman\Api\AbstractCrudMethodsModelInterface
     */
    protected function create($model, $data)
    {
        $model->set_guest_author_name($this->sanitize_string($data->author_name));
        $model->set_message($data->message);

        $model->create();

        // Return created model data
        return $model;
    }
}
