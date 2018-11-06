<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use BetaKiller\Model\UserInterface;
use BetaKiller\Status\StatusWorkflowFactory;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\ArgumentsDefinitionInterface;
use Spotman\Api\ArgumentsInterface;

class PauseApiMethod extends AbstractEntityBasedApiMethod
{
    use ContentPostMethodTrait;

    /**
     * @var \BetaKiller\Status\StatusWorkflowFactory
     */
    private $workflowFactory;

    /**
     * PauseApiMethod constructor.
     *
     * @param \BetaKiller\Status\StatusWorkflowFactory $workflowFactory
     */
    public function __construct(StatusWorkflowFactory $workflowFactory)
    {
        $this->workflowFactory = $workflowFactory;
    }

    /**
     * @return \Spotman\Api\ArgumentsDefinitionInterface
     */
    public function getArgumentsDefinition(): ArgumentsDefinitionInterface
    {
        return $this->definition()
            ->identity();
    }

    /**
     * @param \Spotman\Api\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Status\StatusException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        /** @var \BetaKiller\Model\ContentPost $model */
        $model = $this->getEntity($arguments);

        /** @var \BetaKiller\Status\ContentPostWorkflow $workflow */
        $workflow = $this->workflowFactory->create($model);

        $workflow->pause();

        $this->saveEntity($model);

        return null;
    }
}
