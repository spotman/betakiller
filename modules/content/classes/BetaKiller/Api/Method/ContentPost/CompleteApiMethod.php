<?php
namespace BetaKiller\Api\Method\ContentPost;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use BetaKiller\Api\Method\EntityBasedApiMethodHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Workflow\ContentPostWorkflow;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

final readonly class CompleteApiMethod extends AbstractEntityBasedApiMethod
{
    /**
     * CompleteApiMethod constructor.
     *
     * @param \BetaKiller\Workflow\ContentPostWorkflow          $workflow
     * @param \BetaKiller\Api\Method\EntityBasedApiMethodHelper $helper
     */
    public function __construct(private ContentPostWorkflow $workflow, EntityBasedApiMethodHelper $helper)
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
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Workflow\WorkflowException
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        /** @var \BetaKiller\Model\ContentPostInterface $model */
        $model = $this->getEntity($arguments);

        $this->workflow->complete($model, $user);

        $this->saveEntity($model);

        return null;
    }
}
