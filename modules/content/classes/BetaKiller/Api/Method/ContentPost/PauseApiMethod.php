<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractStatusWorkflowApiMethod;

class PauseApiMethod extends AbstractStatusWorkflowApiMethod
{
    use ContentPostMethodTrait;

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
        /** @var \Model_ContentPost $model */
        $model = $this->getModel();

        $model->pause()->save();

        return null;
    }
}
