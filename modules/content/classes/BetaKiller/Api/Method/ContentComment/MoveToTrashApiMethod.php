<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractStatusWorkflowApiMethod;

class MoveToTrashApiMethod extends AbstractStatusWorkflowApiMethod
{
    use ContentCommentMethodTrait;

    /**
     * MoveToTrashApiMethod constructor.
     *
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = (int) $id;
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute()
    {
        /** @var \Model_ContentComment $model */
        $model = $this->getModel();

        $model->move_to_trash()->save();

        return null;
    }
}
