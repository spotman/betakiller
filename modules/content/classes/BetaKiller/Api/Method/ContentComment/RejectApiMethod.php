<?php
namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use BetaKiller\Model\UserInterface;
use BetaKiller\Workflow\StatusWorkflowFactory;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class RejectApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * @var \BetaKiller\Workflow\StatusWorkflowFactory
     */
    private $workflowFactory;

    /**
     * RejectApiMethod constructor.
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
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Workflow\StatusException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        /** @var \BetaKiller\Model\ContentCommentInterface $model */
        $model = $this->getEntity($arguments);

        /** @var \BetaKiller\Workflow\ContentCommentWorkflow $workflow */
        $workflow = $this->workflowFactory->createFor($model);

        $workflow->reject($model, $user);

        $this->saveEntity($model);

        return null;
    }
}
