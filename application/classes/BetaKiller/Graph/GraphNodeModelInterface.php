<?php
namespace BetaKiller\Graph;

interface GraphNodeModelInterface
{
    /**
     * @return int
     */
    public function get_id();

    /**
     * @return string
     */
    public function get_codename();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_codename($value);

    /**
     * @return string
     */
    public function get_label();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_label($value);

    /**
     * @return NULL|GraphNodeModelInterface
     */
    public function get_start_node();

    /**
     * @return NULL|GraphNodeModelInterface
     */
    public function get_finish_node();

    /**
     * @return $this
     */
    public function filter_start();

    /**
     * @return $this
     */
    public function filter_finish();

    /**
     * @return bool
     */
    public function is_start();

    /**
     * @return bool
     */
    public function is_finish();

    /**
     * @return GraphNodeModelInterface[]
     */
    public function get_source_nodes();

    /**
     * @return GraphNodeModelInterface[]
     */
    public function get_target_nodes();

    /**
     * @param GraphNodeModelInterface|NULL $source
     * @param GraphNodeModelInterface|NULL $target
     *
     * @return GraphTransitionModelInterface[]
     */
    public function get_transitions(GraphNodeModelInterface $source = NULL, GraphNodeModelInterface $target = NULL);

    /**
     * @return GraphTransitionModelInterface[]
     */
    public function get_source_transitions();

    /**
     * @return GraphTransitionModelInterface[]
     */
    public function get_target_transitions();

    /**
     * @param GraphNodeModelInterface $source
     * @param GraphNodeModelInterface $target
     *
     * @return bool
     */
    public function transition_exists(GraphNodeModelInterface $source, GraphNodeModelInterface $target);

    /**
     * @param GraphNodeModelInterface $source
     *
     * @return bool
     */
    public function has_source(GraphNodeModelInterface $source);

    /**
     * @param GraphNodeModelInterface $target
     *
     * @return bool
     */
    public function has_target(GraphNodeModelInterface $target);

    /**
     * @return GraphNodeModelInterface[]
     */
    public function get_all_nodes();
}
