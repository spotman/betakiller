<?php
namespace BetaKiller\Status;

use BetaKiller\Factory\NamespaceBasedFactory;

class StatusWorkflowFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    protected $factory;

    /**
     * StatusWorkflowFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory
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
