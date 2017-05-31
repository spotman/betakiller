<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;

class RestoreFromTrashApiMethod extends AbstractEntityBasedApiMethod
{
    use ContentCommentMethodTrait;

    /**
     * RestoreFromTrashApiMethod constructor.
     *
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = (int)$id;
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute()
    {
        /** @var \Model_ContentComment $model */
        $model = $this->getEntity();

        $model->restore_from_trash()->save();

        return null;
    }
}
