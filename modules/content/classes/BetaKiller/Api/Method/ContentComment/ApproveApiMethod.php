<?php

namespace BetaKiller\Api\Method\ContentComment;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use BetaKiller\Api\Method\EntityBasedApiMethodHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Workflow\ContentCommentWorkflow;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

final readonly class ApproveApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * ApproveApiMethod constructor.
     *
     * @param \BetaKiller\Workflow\ContentCommentWorkflow       $workflow
     * @param \BetaKiller\Api\Method\EntityBasedApiMethodHelper $helper
     */
    public function __construct(private ContentCommentWorkflow $workflow, EntityBasedApiMethodHelper $helper)
    {
        parent::__construct($helper);
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
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
     * @throws \BetaKiller\Workflow\WorkflowException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        /** @var \BetaKiller\Model\ContentCommentInterface $model */
        $model = $this->getEntity($arguments);

        $this->workflow->approve($model, $user);

        $this->saveEntity($model);

        return null;
    }
}
