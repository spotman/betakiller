<?php
namespace BetaKiller\Status;

class StatusWorkflowFactory
{
    use \BetaKiller\Utils\Factory\BaseFactoryTrait,
        \BetaKiller\Utils\Instance\SingletonTrait;

    /**
     * @param string                $name
     * @param StatusRelatedModelInterface $model
     *
     * @return StatusWorkflowInterface
     */
    public function create($name, StatusRelatedModelInterface $model)
    {
        return $this->_create($name, $model);
    }

    protected function make_instance_class_name($name)
    {
        // TODO Base namespace-related factory with DI
        return '\\Status_Workflow_' . $name;
    }

    protected function make_instance($class_name, StatusRelatedModelOrm $model)
    {
        return new $class_name($model);
    }
}
