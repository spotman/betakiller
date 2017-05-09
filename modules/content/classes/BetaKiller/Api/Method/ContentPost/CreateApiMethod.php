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
            $model->setLabel($this->sanitizeString($data->label));
        }

        if (isset($data->type)) {
            $type = $this->sanitizeString($data->type);

            switch ($type) {
                case 'article':
                    $model->markAsArticle();
                    break;

                case 'page':
                    $model->markAsPage();
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
