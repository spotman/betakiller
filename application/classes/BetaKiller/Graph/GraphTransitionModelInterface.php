<?php
namespace BetaKiller\Graph;

/**
 * Interface GraphTransitionModelInterface
 *
 * @package BetaKiller\Graph
 * @deprecated
 */
interface GraphTransitionModelInterface
{
    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * @param string $value
     *
     * @return void
     */
    public function setCodename(string $value): void;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param string $value
     *
     * @return void
     */
    public function setLabel(string $value): void;

    /**
     * @return GraphNodeModelInterface
     */
    public function getSourceNode();

    /**
     * @return GraphNodeModelInterface
     */
    public function getTargetNode();

    /**
     * @param GraphNodeModelInterface $node
     *
     * @return GraphNodeModelInterface[]
     */
    public function getSourceNodes(GraphNodeModelInterface $node): array;

    /**
     * @param GraphNodeModelInterface $node
     *
     * @return GraphNodeModelInterface[]
     */
    public function getTargetNodes(GraphNodeModelInterface $node): array;

    /**
     * @param GraphNodeModelInterface|NULL $source
     * @param GraphNodeModelInterface|NULL $target
     *
     * @return GraphTransitionModelInterface[]
     */
    public function getTransitions(GraphNodeModelInterface $source = null, GraphNodeModelInterface $target = null): array;

    /**
     * @param GraphNodeModelInterface $source
     * @param GraphNodeModelInterface $target
     *
     * @return bool
     */
    public function transitionExists(GraphNodeModelInterface $source, GraphNodeModelInterface $target): bool;
}
