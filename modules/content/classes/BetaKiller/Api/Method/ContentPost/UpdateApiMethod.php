<?php
namespace BetaKiller\Api\Method\ContentPost;

use Spotman\Api\Method\AbstractModelUpdateApiApiMethod;

class UpdateApiMethod extends AbstractModelUpdateApiApiMethod
{
    use ContentPostMethodTrait;

    /**
     * Override this method
     *
     * @param \Model_ContentPost $model
     * @param                    $data
     *
     * @throws \Spotman\Api\ApiMethodException
     * @return \Spotman\Api\AbstractCrudMethodsModelInterface|null
     */
    protected function update($model, $data)
    {
        if (isset($data->label)) {
            $model->set_label($this->sanitize_string($data->label));
        }

        if (isset($data->uri)) {
            $model->set_uri($this->sanitize_string($data->uri));
        }

        if (isset($data->title)) {
            $model->set_title($this->sanitize_string($data->title));
        }

        if (isset($data->description)) {
            $model->set_description($this->sanitize_string($data->description));
        }

        if (isset($data->content)) {
            $model->set_content($data->content);
        }

        $model->update();

        // Return updated model data
        return $model;
    }
}
