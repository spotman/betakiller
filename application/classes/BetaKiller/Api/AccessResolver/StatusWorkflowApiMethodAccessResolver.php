<?php
namespace Spotman\Api\AccessResolver;

use BetaKiller\Status\StatusRelatedModelInterface;
use BetaKiller\Status\StatusWorkflowFactory;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodInterface;
use Spotman\Api\Method\ModelBasedApiMethodInterface;

class StatusWorkflowApiMethodAccessResolver implements ApiMethodAccessResolverInterface
{
    const CODENAME = 'StatusWorkflow';

    /**
     * @var \BetaKiller\Status\StatusWorkflowFactory
     */
    protected $statusWorkflowFactory;

    /**
     * StatusWorkflowApiMethodAccessResolver constructor.
     *
     * @param $statusWorkflowFactory
     */
    public function __construct(StatusWorkflowFactory $statusWorkflowFactory)
    {
        $this->statusWorkflowFactory = $statusWorkflowFactory;
    }

    /**
     * @param \Spotman\Api\ApiMethodInterface $method
     *
     * @return bool
     */
    public function isMethodAllowed(ApiMethodInterface $method)
    {
        if (!($method instanceof ModelBasedApiMethodInterface)) {
            throw new ApiMethodException('Api method [:collection.:method] must implement :interface', [
                ':collection' => $method->getCollectionName(),
                ':method'     => $method->getName(),
                ':interface'  => StatusRelatedModelInterface::class,
            ]);
        }

        $model = $method->getModel();

        if (!($model instanceof StatusRelatedModelInterface)) {
            throw new ApiMethodException('Api method [:collection.:method] must return model of :interface', [
                ':collection' => $method->getCollectionName(),
                ':method'     => $method->getName(),
                ':interface'  => StatusRelatedModelInterface::class,
            ]);
        }

        $workflowName   = $method->getCollectionName();
        $transitionName = $method->getName();

        $workflowInstance = $this->statusWorkflowFactory->create($workflowName, $model);

        return $workflowInstance->isTransitionAllowed($transitionName);
    }
}
