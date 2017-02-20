<?php
namespace BetaKiller\Graph;

interface GraphTransitionModelInterface
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
     * @return GraphNodeModelInterface
     */
    public function get_source_node();

    /**
     * @return GraphNodeModelInterface
     */
    public function get_target_node();

    /**
     * @param GraphNodeModelInterface $node
     *
     * @return GraphNodeModelInterface[]
     */
    public function get_source_nodes(GraphNodeModelInterface $node);

    /**
     * @param GraphNodeModelInterface $node
     *
     * @return GraphNodeModelInterface[]
     */
    public function get_target_nodes(GraphNodeModelInterface $node);

    /**
     * @param GraphNodeModelInterface|NULL $source
     * @param GraphNodeModelInterface|NULL $target
     *
     * @return GraphTransitionModelInterface[]
     */
    public function get_transitions(GraphNodeModelInterface $source = NULL, GraphNodeModelInterface $target = NULL);

    /**
     * @param GraphNodeModelInterface $source
     * @param GraphNodeModelInterface $target
     *
     * @return bool
     */
    public function transition_exists(GraphNodeModelInterface $source, GraphNodeModelInterface $target);
}
