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

        $model->update();

        // Return updated model data
        return $model;
    }
}
