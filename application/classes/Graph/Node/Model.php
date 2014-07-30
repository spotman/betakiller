<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Graph_Node_Model extends ORM {

    /**
     * @return NULL|Graph_Node_Model
     */
    public function get_start_node()
    {
        $node = $this->node_model_factory()->filter_start()->cached()->find();

        return $node->loaded() ? $node : NULL;
    }

    /**
     * @return NULL|Graph_Node_Model
     */
    public function get_finish_node()
    {
        $node = $this->node_model_factory()->filter_finish()->cached()->find();

        return $node->loaded() ? $node : NULL;
    }

    public function filter_start()
    {
        return $this->where($this->get_start_marker_column_name(), '=', TRUE);
    }

    public function filter_finish()
    {
        return $this->where($this->get_finish_marker_column_name(), '=', TRUE);
    }

    public function is_start()
    {
        return !! $this->get($this->get_start_marker_column_name());
    }

    public function is_finish()
    {
        return !! $this->get($this->get_finish_marker_column_name());
    }

    /**
     * @return Graph_Node_Model[]|NULL
     */
    public function get_source_nodes()
    {
        return $this->transition_model_factory()->get_source_nodes($this);
    }

    /**
     * @return Graph_Node_Model[]|NULL
     */
    public function get_target_nodes()
    {
        return $this->transition_model_factory()->get_target_nodes($this);
    }

    public function get_transitions(Graph_Node_Model $source = NULL, Graph_Node_Model $target = NULL)
    {
        return $this->transition_model_factory()->get_transitions($source, $target);
    }

    public function get_source_transitions()
    {
        return $this->get_transitions(NULL, $this);
    }

    public function get_target_transitions()
    {
        return $this->get_transitions($this, NULL);
    }

    public function transition_exists(Graph_Node_Model $source, Graph_Node_Model $target)
    {
        return $this->transition_model_factory()->transition_exists($source, $target);
    }

    public function has_source(Graph_Node_Model $source)
    {
        return $this->transition_exists($source, $this);
    }

    public function has_target(Graph_Node_Model $target)
    {
        return $this->transition_exists($this, $target);
    }

    /**
     * @param int|array|NULL $id
     * @return Graph_Transition_Model
     */
    protected function transition_model_factory($id = NULL)
    {
        return ORM::factory($this->get_transition_model_name(), $id);
    }

    /**
     * @param int|array|NULL $id
     * @return Graph_Node_Model
     */
    protected function node_model_factory($id = NULL)
    {
        return ORM::factory($this->object_name(), $id);
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