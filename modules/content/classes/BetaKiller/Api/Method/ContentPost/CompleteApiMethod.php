<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use BetaKiller\Status\StatusWorkflowFactory;
use Spotman\Api\ApiMethodResponse;

class CompleteApiMethod extends AbstractEntityBasedApiMethod
{
    use ContentPostMethodTrait;

    /**
     * @var \BetaKiller\Status\StatusWorkflowFactory
     */
    private $workflowFactory;

    /**
     * ApproveApiMethod constructor.
     *
     * @param int                                      $id
     * @param \BetaKiller\Status\StatusWorkflowFactory $workflowFactory
     */
    public function __construct($id, StatusWorkflowFactory $workflowFactory)
    {
        $this->id = (int)$id;
        $this->workflowFactory = $workflowFactory;
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \Kohana_Exception
     * @throws \HTTP_Exception_501
     * @throws \BetaKiller\Status\StatusWorkflowException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Status\StatusException
     */
    public function execute(): ?ApiMethodResponse
    {
        /** @var \BetaKiller\Model\ContentPost $model */
        $model = $this->getEntity();

        /** @var \BetaKiller\Status\ContentPostWorkflow $workflow */
        $workflow = $this->workflowFactory->create($model);

        $workflow->complete();

        $this->saveEntity();

        return null;
    }
}
