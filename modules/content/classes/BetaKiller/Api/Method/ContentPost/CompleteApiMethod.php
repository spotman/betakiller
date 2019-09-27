<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use BetaKiller\Model\UserInterface;
use BetaKiller\Workflow\StatusWorkflowFactory;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class CompleteApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * @var \BetaKiller\Workflow\StatusWorkflowFactory
     */
    private $workflowFactory;

    /**
     * ApproveApiMethod constructor.
     *
     * @param \BetaKiller\Workflow\StatusWorkflowFactory $workflowFactory
     */
    public function __construct(StatusWorkflowFactory $workflowFactory)
    {
        $this->workflowFactory = $workflowFactory;
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition()
            ->identity();
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Workflow\StatusException
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        /** @var \BetaKiller\Model\ContentPostInterface $model */
        $model = $this->getEntity($arguments);

        /** @var \BetaKiller\Workflow\ContentPostWorkflow $workflow */
        $workflow = $this->workflowFactory->createFor($model);

        $workflow->complete($model, $user);

        $this->saveEntity($model);

        return null;
    }
}
