<?php
namespace BetaKiller\Status;

use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\DI\Container;

class StatusWorkflowFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    protected $factory;

    /**
     * @return \BetaKiller\Status\StatusWorkflowFactory
     * @deprecated Use DI instead
     */
    public static function instance()
    {
        return Container::instance()->get(static::class);
    }

    /**
     * StatusWorkflowFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory
            ->setClassPrefixes('Status', 'Workflow')
            ->setExpectedInterface(StatusWorkflowInterface::class);
    }

    /**
     * @param string                $name
     * @param StatusRelatedModelInterface $model
     *
     * @return StatusWorkflowInterface
     */
    public function create($name, StatusRelatedModelInterface $model)
    {
        return $this->factory->create($name, ['model' => $model]);
    }
}
