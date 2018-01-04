<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use BetaKiller\Status\StatusWorkflowFactory;
use Spotman\Api\ApiMethodResponse;

class MarkAsSpamApiMethod extends AbstractEntityBasedApiMethod
{
    use ContentCommentMethodTrait;

    /**
     * @var \BetaKiller\Status\StatusWorkflowFactory
     */
    private $workflowFactory;

    /**
     * MarkAsSpamApiMethod constructor.
     *
     * @param int                                      $id
     * @param \BetaKiller\Status\StatusWorkflowFactory $workflowFactory
     */
    public function __construct($id, StatusWorkflowFactory $workflowFactory)
    {
        $this->id              = (int)$id;
        $this->workflowFactory = $workflowFactory;
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(): ?ApiMethodResponse
    {
        /** @var \BetaKiller\Model\ContentCommentInterface $model */
        $model = $this->getEntity();

        /** @var \BetaKiller\Status\ContentCommentWorkflow $workflow */
        $workflow = $this->workflowFactory->create($model);

        $workflow->markAsSpam();

        $this->saveEntity();

        return null;
    }
}
