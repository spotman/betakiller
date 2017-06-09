<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Helper\ContentTrait;

trait ContentCommentMethodTrait
{
    use ContentTrait;

    /**
     * Returns new model or performs search by id
     *
     * @param int|null $id
     *
     * @return \Model_ContentComment
     */
    protected function createEntity($id = NULL)
    {
        return $this->model_factory_content_comment($id);
    }

    protected function sanitize_string($value)
    {
        return \HTML::chars(trim(strip_tags($value)));
    }
}
