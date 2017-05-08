<?php
namespace BetaKiller\Api\Method\ContentPost;

use Spotman\Api\ApiMethodException;
use Spotman\Api\Method\AbstractModelCreateApiMethod;

class CreateApiMethod extends AbstractModelCreateApiMethod
{
    use ContentPostMethodTrait;

    /**
     * Implement this method
     *
     * @param \Model_ContentPost    $model
     * @param                       $data
     *
     * @throws \Spotman\Api\ApiMethodException
     * @return \Spotman\Api\AbstractCrudMethodsModelInterface
     */
    protected function create($model, $data)
    {
        $model->draft();

        if (isset($data->label)) {
            $model->setLabel($this->sanitize_string($data->label));
        }

        if (isset($data->type)) {
            $type = $this->sanitize_string($data->type);

            switch ($type) {
                case 'article':
                    $model->mark_as_article();
                    break;

                case 'page':
                    $model->mark_as_page();
                    break;

                default:
                    throw new ApiMethodException('Unknown content post type :value', [':value' => $type]);
            }
        }

        $model->create();

        // Return created model data
        return $model;
    }
}
