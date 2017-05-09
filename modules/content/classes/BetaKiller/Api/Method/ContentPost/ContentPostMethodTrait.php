<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Helper\ContentTrait;

trait ContentPostMethodTrait
{
    use ContentTrait;

    /**
     * Returns new model or performs search by id
     *
     * @param int|null $id
     *
     * @return \Model_ContentPost
     */
    protected function modelFactory($id = null)
    {
        return $this->model_factory_content_post($id);
    }

    protected function sanitizeString($value)
    {
        return \HTML::chars(trim(strip_tags($value)));
    }
}
