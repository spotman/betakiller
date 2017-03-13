<?php
namespace BetaKiller\Graph;

use ORM;

abstract class GraphTransitionModelOrm extends ORM implements GraphTransitionModelInterface
{
    protected function _initialize()
    {
        $source_node_key = $this->get_source_node_relation_key();
        $target_node_key = $this->get_target_node_relation_key();

        $this->belongs_to([

            $source_node_key => [
                'model'       => $this->get_node_model_name(),
                'foreign_key' => $this->get_source_node_relation_fk(),
            ],

            $target_node_key => [
                'model'       => $this->get_node_model_name(),
                'foreign_key' => $this->get_target_node_relation_fk(),
            ],

        ]);

        $this->load_with([$source_node_key, $target_node_key]);

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
     * @return GraphNodeModelInterface
     */
    protected function get_source_node_relation()
    {
        return $this->get($this->get_source_node_relation_key());
    }

    /**
     * @return GraphNodeModelInterface
     */
    protected function get_target_node_relation()
    {
        return $this->get($this->get_target_node_relation_key());
    }

    /**
     * @return \BetaKiller\Graph\GraphNodeModelInterface
     */
    public function get_source_node()
    {
        return $this->get_source_node_relation();
    }

    /**
     * @return \BetaKiller\Graph\GraphNodeModelInterface
     */
    public function get_target_node()
    {
        return $this->get_target_node_relation();
    }

    /**
     * @param GraphNodeModelInterface $node
     *
     * @return GraphNodeModelInterface[]
     */
    public function get_source_nodes(GraphNodeModelInterface $node)
    {
        return $this
            ->filter_target_node($node)
            ->cached()
            ->find_all()
            ->as_array(NULL, $this->get_source_node_relation_key());
    }

    /**
     * @param GraphNodeModelInterface $node
     *
     * @return GraphNodeModelInterface[]
     */
    public function get_target_nodes(GraphNodeModelInterface $node)
    {
        return $this
            ->filter_source_node($node)
            ->cached()
            ->find_all()
            ->as_array(NULL, $this->get_target_node_relation_key());
    }

    /**
     * @param GraphNodeModelInterface|NULL $source
     * @param GraphNodeModelInterface|NULL $target
     *
     * @return GraphTransitionModelInterface[]
     */
    public function get_transitions(GraphNodeModelInterface $source = NULL, GraphNodeModelInterface $target = NULL)
    {
        if ($source) {
            $this->filter_source_node($source);
        }

        if ($target) {
            $this->filter_target_node($target);
        }

        return $this->cached()->find_all()->as_array();
    }

    /**
     * @param GraphNodeModelInterface $source
     * @param GraphNodeModelInterface $target
     *
     * @return bool
     */
    public function transition_exists(GraphNodeModelInterface $source, GraphNodeModelInterface $target)
    {
        return count($this->get_transitions($source, $target)) > 0;
    }

    /**
     * @param GraphNodeModelInterface $source
     *
     * @return GraphTransitionModelOrm
     */
    protected function filter_source_node(GraphNodeModelInterface $source)
    {
        return $this->where($this->get_source_node_relation_fk(), '=', $source->get_id());
    }

    /**
     * @param GraphNodeModelInterface $target
     *
     * @return GraphTransitionModelOrm
     */
    protected function filter_target_node(GraphNodeModelInterface $target)
    {
        return $this->where($this->get_target_node_relation_fk(), '=', $target->get_id());
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
