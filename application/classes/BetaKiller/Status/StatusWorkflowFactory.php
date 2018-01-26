<?php
namespace BetaKiller\Status;

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
            ->setClassNamespaces(StatusWorkflowInterface::CLASS_NS)
            ->setClassSuffix(StatusWorkflowInterface::CLASS_SUFFIX)
            ->setExpectedInterface(StatusWorkflowInterface::class);
    }

    /**
     * @param StatusRelatedModelInterface $model
     *
     * @return StatusWorkflowInterface|mixed
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(StatusRelatedModelInterface $model)
    {
        return $this->factory->create($model->getWorkflowName(), ['model' => $model]);
    }
}
