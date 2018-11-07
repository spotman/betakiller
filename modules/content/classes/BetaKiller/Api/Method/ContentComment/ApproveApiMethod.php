<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use BetaKiller\Model\UserInterface;
use BetaKiller\Status\StatusWorkflowFactory;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsDefinitionInterface;
use Spotman\Defence\ArgumentsInterface;

class ApproveApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * @var \BetaKiller\Status\StatusWorkflowFactory
     */
    private $workflowFactory;

    /**
     * ApproveApiMethod constructor.
     *
     * @param \BetaKiller\Status\StatusWorkflowFactory $workflowFactory
     */
    public function __construct(StatusWorkflowFactory $workflowFactory)
    {
        $this->workflowFactory = $workflowFactory;
    }

    /**
     * @return \Spotman\Defence\ArgumentsDefinitionInterface
     */
    public function getArgumentsDefinition(): ArgumentsDefinitionInterface
    {
        return $this->definition()
            ->identity();
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Status\StatusException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        /** @var \BetaKiller\Model\ContentCommentInterface $model */
        $model = $this->getEntity($arguments);

        /** @var \BetaKiller\Status\ContentCommentWorkflow $workflow */
        $workflow = $this->workflowFactory->create($model);

        $workflow->approve();

        $this->saveEntity($model);

        return null;
    }
}
