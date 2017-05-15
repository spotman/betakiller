<?php
namespace BetaKiller\Graph;

use ORM;

abstract class GraphNodeModelOrm extends ORM implements GraphNodeModelInterface
{
    /**
     * @return string
     */
    public function get_codename()
    {
        return $this->get('codename');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_codename($value)
    {
        return $this->set('codename', $value);
    }

    /**
     * @return string
     */
    public function get_label()
    {
        return $this->get('label');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_label($value)
    {
        return $this->set('label', $value);
    }

    /**
     * @return NULL|\BetaKiller\Graph\GraphNodeModelInterface
     */
    public function get_start_node()
    {
        $node = $this->node_model_factory()->filter_start()->cached()->find();

        return $node->loaded() ? $node : null;
    }

    /**
     * @return NULL|GraphNodeModelOrm
     */
    public function get_finish_node()
    {
        $node = $this->node_model_factory()->filter_finish()->cached()->find();

        return $node->loaded() ? $node : null;
    }

    public function filter_start()
    {
        return $this->where($this->get_start_marker_column_name(), '=', true);
    }

    public function filter_finish()
    {
        return $this->where($this->get_finish_marker_column_name(), '=', true);
    }

    public function is_start()
    {
        return (bool)$this->get($this->get_start_marker_column_name());
    }

    public function is_finish()
    {
        return (bool)$this->get($this->get_finish_marker_column_name());
    }

    /**
     * @return \BetaKiller\Graph\GraphNodeModelInterface[]|NULL
     */
    public function get_source_nodes()
    {
        return $this->transition_model_factory()->get_source_nodes($this);
    }

    /**
     * @return GraphNodeModelInterface[]
     */
    public function get_target_nodes()
    {
        return $this->transition_model_factory()->get_target_nodes($this);
    }

    public function get_transitions(GraphNodeModelInterface $source = null, GraphNodeModelInterface $target = null)
    {
        return $this->transition_model_factory()->get_transitions($source, $target);
    }

    public function get_source_transitions()
    {
        return $this->get_transitions(null, $this);
    }

    public function get_target_transitions()
    {
        return $this->get_transitions($this, null);
    }

    public function transition_exists(GraphNodeModelInterface $source, GraphNodeModelInterface $target)
    {
        return $this->transition_model_factory()->transition_exists($source, $target);
    }

    public function has_source(GraphNodeModelInterface $source)
    {
        return $this->transition_exists($source, $this);
    }

    public function has_target(GraphNodeModelInterface $target)
    {
        return $this->transition_exists($this, $target);
    }

    /**
     * @return GraphNodeModelInterface[]
     */
    public function get_all_nodes()
    {
        return $this->model_factory()->find_all()->as_array();
    }

    /**
     * Override this method if you need custom transition filtering
     *
     * @param int|array|NULL $id
     *
     * @return GraphTransitionModelOrm|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function transition_model_factory($id = null)
    {
        return $this->model_factory($id, $this->get_transition_model_name());
    }

    /**
     * @param int|array|NULL $id
     *
     * @return GraphNodeModelOrm|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function node_model_factory($id = null)
    {
        return $this->model_factory($id);
    }

    protected function get_start_marker_column_name()
    {
        return 'is_start';
    }

    protected function get_finish_marker_column_name()
    {
        return 'is_finish';
    }

    /**
     * @return string
     */
    abstract protected function get_transition_model_name();
}
