<?php
namespace BetaKiller\Graph;

use ORM;

abstract class AbstractGraphNodeModelOrm extends ORM implements GraphNodeModelInterface
{
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
     * @return NULL|\BetaKiller\Graph\GraphNodeModelInterface
     */
    public function getStartNode()
    {
        $node = $this->nodeModelFactory()->filterStart()->cached()->find();

        return $node->loaded() ? $node : null;
    }

    /**
     * @return NULL|AbstractGraphNodeModelOrm
     */
    public function getFinishNode()
    {
        $node = $this->nodeModelFactory()->filterFinish()->cached()->find();

        return $node->loaded() ? $node : null;
    }

    public function filterStart()
    {
        return $this->where($this->getStartMarkerColumnName(), '=', true);
    }

    public function filterFinish()
    {
        return $this->where($this->getFinishMarkerColumnName(), '=', true);
    }

    public function isStart(): bool
    {
        return (bool)$this->get($this->getStartMarkerColumnName());
    }

    public function isFinish(): bool
    {
        return (bool)$this->get($this->getFinishMarkerColumnName());
    }

    /**
     * @return \BetaKiller\Graph\GraphNodeModelInterface[]|NULL
     * @throws \Kohana_Exception
     */
    public function getSourceNodes(): array
    {
        return $this->transitionModelFactory()->getSourceNodes($this);
    }

    /**
     * @return GraphNodeModelInterface[]
     * @throws \Kohana_Exception
     */
    public function getTargetNodes(): array
    {
        return $this->transitionModelFactory()->getTargetNodes($this);
    }

    public function getTransitions(GraphNodeModelInterface $source = null, GraphNodeModelInterface $target = null): array
    {
        return $this->transitionModelFactory()->getTransitions($source, $target);
    }

    public function getSourceTransitions(): array
    {
        return $this->getTransitions(null, $this);
    }

    public function getTargetTransitions(): array
    {
        return $this->getTransitions($this, null);
    }

    public function transitionExists(GraphNodeModelInterface $source, GraphNodeModelInterface $target): bool
    {
        return $this->transitionModelFactory()->transitionExists($source, $target);
    }

    public function hasSource(GraphNodeModelInterface $source): bool
    {
        return $this->transitionExists($source, $this);
    }

    public function hasTarget(GraphNodeModelInterface $target): bool
    {
        return $this->transitionExists($this, $target);
    }

    /**
     * @return GraphNodeModelInterface[]
     * @throws \Kohana_Exception
     */
    public function getAllNodes(): array
    {
        return $this->model_factory()->find_all()->as_array();
    }

    /**
     * Override this method if you need custom transition filtering
     *
     * @param int|array|NULL $id
     *
     * @return AbstractGraphTransitionModelOrm|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function transitionModelFactory($id = null)
    {
        return $this->model_factory($id, $this->getTransitionModelName());
    }

    /**
     * @param int|array|NULL $id
     *
     * @return AbstractGraphNodeModelOrm|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    protected function nodeModelFactory($id = null)
    {
        return $this->model_factory($id);
    }

    protected function getStartMarkerColumnName(): string
    {
        return 'is_start';
    }

    protected function getFinishMarkerColumnName(): string
    {
        return 'is_finish';
    }

    /**
     * @return string
     */
    abstract protected function getTransitionModelName(): string;
}
