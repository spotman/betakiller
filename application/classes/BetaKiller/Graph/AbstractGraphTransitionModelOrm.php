<?php
namespace BetaKiller\Graph;

use ORM;

abstract class AbstractGraphTransitionModelOrm extends ORM implements GraphTransitionModelInterface
{
    protected function configure(): void
    {
        $source_node_key = $this->getSourceNodeRelationKey();
        $target_node_key = $this->getTargetNodeRelationKey();

        $this->belongs_to([

            $source_node_key => [
                'model'       => $this->getNodeModelName(),
                'foreign_key' => $this->getSourceNodeRelationFk(),
            ],

            $target_node_key => [
                'model'       => $this->getNodeModelName(),
                'foreign_key' => $this->getTargetNodeRelationFk(),
            ],

        ]);

        $this->load_with([$source_node_key, $target_node_key]);

        parent::configure();
    }

    /**
     * @return string
     * @throws \Kohana_Exception
     */
    public function getCodename(): string
    {
        return $this->get('codename');
    }

    /**
     * @param string $value
     *
     * @return void
     * @throws \Kohana_Exception
     */
    public function setCodename(string $value): void
    {
        $this->set('codename', $value);
    }

    /**
     * @return string
     * @throws \Kohana_Exception
     */
    public function getLabel(): string
    {
        return $this->get('label');
    }

    /**
     * @param string $value
     *
     * @return void
     * @throws \Kohana_Exception
     */
    public function setLabel(string $value): void
    {
        $this->set('label', $value);
    }

    /**
     * @return GraphNodeModelInterface
     */
    protected function getSourceNodeRelation()
    {
        return $this->get($this->getSourceNodeRelationKey());
    }

    /**
     * @return GraphNodeModelInterface
     */
    protected function getTargetNodeRelation()
    {
        return $this->get($this->getTargetNodeRelationKey());
    }

    /**
     * @return \BetaKiller\Graph\GraphNodeModelInterface
     */
    public function getSourceNode()
    {
        return $this->getSourceNodeRelation();
    }

    /**
     * @return \BetaKiller\Graph\GraphNodeModelInterface
     */
    public function getTargetNode()
    {
        return $this->getTargetNodeRelation();
    }

    /**
     * @param GraphNodeModelInterface $node
     *
     * @return GraphNodeModelInterface[]
     * @throws \Kohana_Exception
     */
    public function getSourceNodes(GraphNodeModelInterface $node): array
    {
        return $this
            ->filterTargetNode($node)
            ->cached()
            ->find_all()
            ->as_array(null, $this->getSourceNodeRelationKey());
    }

    /**
     * @param GraphNodeModelInterface $node
     *
     * @return GraphNodeModelInterface[]
     * @throws \Kohana_Exception
     */
    public function getTargetNodes(GraphNodeModelInterface $node): array
    {
        return $this
            ->filterSourceNode($node)
            ->cached()
            ->find_all()
            ->as_array(null, $this->getTargetNodeRelationKey());
    }

    /**
     * @param GraphNodeModelInterface|NULL $source
     * @param GraphNodeModelInterface|NULL $target
     *
     * @return GraphTransitionModelInterface[]
     * @throws \Kohana_Exception
     */
    public function getTransitions(
        GraphNodeModelInterface $source = null,
        GraphNodeModelInterface $target = null
    ): array {
        if ($source) {
            $this->filterSourceNode($source);
        }

        if ($target) {
            $this->filterTargetNode($target);
        }

        return $this->cached()->find_all()->as_array();
    }

    /**
     * @param GraphNodeModelInterface $source
     * @param GraphNodeModelInterface $target
     *
     * @return bool
     */
    public function transitionExists(GraphNodeModelInterface $source, GraphNodeModelInterface $target): bool
    {
        return count($this->getTransitions($source, $target)) > 0;
    }

    /**
     * @param GraphNodeModelInterface $source
     *
     * @return AbstractGraphTransitionModelOrm
     */
    protected function filterSourceNode(GraphNodeModelInterface $source)
    {
        return $this->where($this->getSourceNodeRelationFk(), '=', $source->getID());
    }

    /**
     * @param GraphNodeModelInterface $target
     *
     * @return AbstractGraphTransitionModelOrm
     */
    protected function filterTargetNode(GraphNodeModelInterface $target)
    {
        return $this->where($this->getTargetNodeRelationFk(), '=', $target->getID());
    }

    /**
     * @return string
     */
    protected function getSourceNodeRelationKey(): string
    {
        return 'source';
    }

    /**
     * @return string
     */
    protected function getTargetNodeRelationKey(): string
    {
        return 'target';
    }

    /**
     * @return string
     */
    protected function getSourceNodeRelationFk(): string
    {
        return 'source_id';
    }

    /**
     * @return string
     */
    protected function getTargetNodeRelationFk(): string
    {
        return 'target_id';
    }

    /**
     * @return string
     */
    abstract protected function getNodeModelName(): string;
}
