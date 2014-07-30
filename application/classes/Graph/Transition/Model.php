<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Graph_Transition_Model extends ORM {

    protected function _initialize()
    {
        $source_node_key        =   $this->get_source_node_relation_key();
        $target_node_key        =   $this->get_target_node_relation_key();

        $this->has_many([

            $source_node_key    =>  [
                'model'         =>  $this->get_node_model_name(),
                'foreign_key'   =>  $this->get_source_node_relation_fk(),
            ],

            $target_node_key    =>  [
                'model'         =>  $this->get_node_model_name(),
                'foreign_key'   =>  $this->get_target_node_relation_fk(),
            ],

        ]);

        parent::_initialize();
    }

    /**
     * @return string
     */
    public function get_codename()
    {
        return $this->get('codename');
    }

    /**
     * @param string $value
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
     * @return $this
     */
    public function set_label($value)
    {
        return $this->set('label', $value);
    }

    /**
     * @param Graph_Node_Model $node
     * @return Graph_Node_Model[]
     */
    public function get_source_nodes(Graph_Node_Model $node)
    {
        return $this
            ->filter_target_node($node)
            ->cached()
            ->find_all()
            ->as_array(NULL, $this->get_source_node_relation_key());
    }

    /**
     * @param Graph_Node_Model $node
     * @return Graph_Node_Model[]
     */
    public function get_target_nodes(Graph_Node_Model $node)
    {
        return $this
            ->filter_source_node($node)
            ->cached()
            ->find_all()
            ->as_array(NULL, $this->get_target_node_relation_key());
    }

    /**
     * @param Graph_Node_Model|NULL $source
     * @param Graph_Node_Model|NULL $target
     * @return Graph_Transition_Model[]|Database_Result
     */
    public function get_transitions(Graph_Node_Model $source = NULL, Graph_Node_Model $target = NULL)
    {
        if ( $source )
            $this->filter_source_node($source);

        if ( $target )
            $this->filter_target_node($target);

        return $this->cached()->find_all();
    }

    /**
     * @param Graph_Node_Model $source
     * @param Graph_Node_Model $target
     * @return bool
     */
    public function transition_exists(Graph_Node_Model $source, Graph_Node_Model $target)
    {
        return !! $this->get_transitions($source, $target)->count();
    }

    /**
     * @param Graph_Node_Model $source
     * @return Graph_Transition_Model
     */
    public function filter_source_node(Graph_Node_Model $source)
    {
        return $this->where($this->get_source_node_relation_fk(), '=', $source->pk());
    }

    /**
     * @param Graph_Node_Model $target
     * @return Graph_Transition_Model
     */
    public function filter_target_node(Graph_Node_Model $target)
    {
        return $this->where($this->get_target_node_relation_fk(), '=', $target->pk());
    }

    /**
     * @return string
     */
    protected function get_source_node_relation_key()
    {
        return 'source';
    }

    /**
     * @return string
     */
    protected function get_target_node_relation_key()
    {
        return 'target';
    }

    /**
     * @return string
     */
    protected function get_source_node_relation_fk()
    {
        return 'source_id';
    }

    /**
     * @return string
     */
    protected function get_target_node_relation_fk()
    {
        return 'target_id';
    }

    /**
     * @return string
     */
    abstract protected function get_node_model_name();

}