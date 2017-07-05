<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityUpdateApiApiMethod;

class UpdateApiMethod extends AbstractEntityUpdateApiApiMethod
{
    use ContentPostMethodTrait;

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\ContentPost $model
     * @param                    $data
     *
     * @throws \Spotman\Api\ApiMethodException
     * @return \BetaKiller\Model\AbstractEntityInterface|null
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
