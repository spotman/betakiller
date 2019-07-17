<?php
namespace BetaKiller\Graph;

/**
 * Interface GraphNodeModelInterface
 *
 * @package BetaKiller\Graph
 * @deprecated
 */
interface GraphNodeModelInterface
{
    /**
     * @return int
     */
    public function getID();

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
     * @return NULL|GraphNodeModelInterface
     */
    public function getStartNode();

    /**
     * @return NULL|GraphNodeModelInterface
     */
    public function getFinishNode();

    /**
     * @return $this
     */
    public function filterStart();

    /**
     * @return $this
     */
    public function filterFinish();

    /**
     * @return bool
     */
    public function isStart(): bool;

    /**
     * @return bool
     */
    public function isFinish(): bool;

    /**
     * @return GraphNodeModelInterface[]
     */
    public function getSourceNodes(): array;

    /**
     * @return GraphNodeModelInterface[]
     */
    public function getTargetNodes(): array;

    /**
     * @param GraphNodeModelInterface|NULL $source
     * @param GraphNodeModelInterface|NULL $target
     *
     * @return GraphTransitionModelInterface[]
     */
    public function getTransitions(GraphNodeModelInterface $source = NULL, GraphNodeModelInterface $target = NULL): array;

    /**
     * @return GraphTransitionModelInterface[]
     */
    public function getSourceTransitions(): array;

    /**
     * @return GraphTransitionModelInterface[]
     */
    public function getTargetTransitions(): array;

    /**
     * @param GraphNodeModelInterface $source
     * @param GraphNodeModelInterface $target
     *
     * @return bool
     */
    public function transitionExists(GraphNodeModelInterface $source, GraphNodeModelInterface $target): bool;

    /**
     * @param GraphNodeModelInterface $source
     *
     * @return bool
     */
    public function hasSource(GraphNodeModelInterface $source): bool;

    /**
     * @param GraphNodeModelInterface $target
     *
     * @return bool
     */
    public function hasTarget(GraphNodeModelInterface $target): bool;

    /**
     * @return GraphNodeModelInterface[]
     */
    public function getAllNodes(): array;
}
