<?php
namespace BetaKiller\Workflow;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;

class StatusWorkflowFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    protected $factory;

    /**
     * StatusWorkflowFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->cacheInstances()
            ->setClassNamespaces(StatusWorkflowInterface::CLASS_NS)
            ->setClassSuffix(StatusWorkflowInterface::CLASS_SUFFIX)
            ->setExpectedInterface(StatusWorkflowInterface::class);
    }

    /**
     * @param HasWorkflowStateModelInterface $model
     *
     * @return StatusWorkflowInterface|mixed
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFor(HasWorkflowStateModelInterface $model)
    {
        return $this->factory->create($model::getModelName());
    }
}
