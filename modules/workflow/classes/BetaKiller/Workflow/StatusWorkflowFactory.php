<?php
namespace BetaKiller\Workflow;

use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;

class StatusWorkflowFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    protected NamespaceBasedFactoryInterface $factory;

    /**
     * StatusWorkflowFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->cacheInstances()
            ->setClassNamespaces(StatusWorkflowInterface::CLASS_NS)
            ->setClassSuffix(StatusWorkflowInterface::CLASS_SUFFIX)
            ->setExpectedInterface(StatusWorkflowInterface::class);
    }

    /**
     * @param HasWorkflowStateInterface $model
     *
     * @return StatusWorkflowInterface|mixed
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFor(HasWorkflowStateInterface $model)
    {
        return $this->factory->create($model::getModelName());
    }
}
