<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractStatusWorkflowApiMethod;

class ApproveApiMethod extends AbstractStatusWorkflowApiMethod
{
    use ContentCommentMethodTrait;

    /**
     * ApproveApiMethod constructor.
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
        $model = $this->getModel();

        $model->approve()->save();

        return null;
    }
}
