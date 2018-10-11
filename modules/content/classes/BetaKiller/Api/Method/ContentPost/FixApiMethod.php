<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use BetaKiller\Status\StatusWorkflowFactory;
use Spotman\Api\ApiMethodResponse;

class FixApiMethod extends AbstractEntityBasedApiMethod
{
    use ContentPostMethodTrait;

    /**
     * @var \BetaKiller\Status\StatusWorkflowFactory
     */
    private $workflowFactory;

    /**
     * FixApiMethod constructor.
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
     * @throws \BetaKiller\Status\StatusException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function execute(): ?ApiMethodResponse
    {
        /** @var \BetaKiller\Model\ContentPost $model */
        $model = $this->getEntity();

        /** @var \BetaKiller\Status\ContentPostWorkflow $workflow */
        $workflow = $this->workflowFactory->create($model);

        $workflow->fix();

        $this->saveEntity();

        return null;
    }
}
