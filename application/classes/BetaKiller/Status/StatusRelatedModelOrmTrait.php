<?php
namespace BetaKiller\Status;

use BetaKiller\Acl\Resource\StatusRelatedEntityAclResourceInterface;
use BetaKiller\Graph\GraphTransitionModelInterface;
use BetaKiller\Model\UserInterface;
use ORM;

trait StatusRelatedModelOrmTrait
{
    protected function initializeRelatedModelRelation()
    {
        $statusRelationKey = $this->getStatusRelationKey();

        $this->belongs_to([
            $statusRelationKey => [
                'model'       => $this->getStatusRelationModelName(),
                'foreign_key' => $this->getStatusRelationForeignKey(),
            ],
        ]);

        $this->load_with([$statusRelationKey]);
    }

    /**
     * @return StatusModelInterface
     */
    public function getCurrentStatus()
    {
        return $this->getStatusRelation();
    }

    /**
     * @param \BetaKiller\Status\StatusModelInterface $target
     *
     * @return $this
     * @throws \BetaKiller\Status\StatusException
     */
    public function changeStatus(StatusModelInterface $target)
    {
        $current = $this->getCurrentStatus();

        // Check if model has current status
        if (!$current->getID()) {
            throw new StatusException('Model must have current status before changing it');
        }

        if (!$current->hasTarget($target)) {
            throw new StatusException('Target status :target is not allowed', [':target' => $target->getCodename()]);
        }

        return $this->setCurrentStatus($target);
    }

    /**
     * @param \BetaKiller\Status\StatusModelInterface $status
     *
     * @return $this
     * @throws \BetaKiller\Status\StatusException
     */
    public function initStatus(StatusModelInterface $status)
    {
        $current = $this->getCurrentStatus();

        // Check if model has no current status
        if ($current->getID()) {
            throw new StatusException('Model can not have current status before initializing');
        }

        return $this->setCurrentStatus($status);
    }

    /**
     * @param int $id
     *
     * @return \BetaKiller\Status\StatusModelInterface
     */
    public function getStatusByID($id)
    {
        return $this->getStatusRelation()->model_factory($id);
    }

    /**
     * @param \BetaKiller\Status\StatusTransitionModelInterface $transition
     *
     * @throws \BetaKiller\Status\StatusException
     */
    public function doStatusTransition(StatusTransitionModelInterface $transition): void
    {
        if ($transition->getSourceNode()->getID() !== $this->getCurrentStatus()->getID()) {
            throw new StatusException('Only transitions from current status are allowed');
        }

        /** @var StatusModelInterface $target_status */
        $target_status = $transition->getTargetNode();

        $this->setCurrentStatus($target_status);
    }

    /**
     * @param string                          $codename
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function isStatusTransitionAllowed(string $codename, UserInterface $user): bool
    {
        return $this->getCurrentStatus()->isTargetTransitionAllowed($codename, $user);
    }

    /**
     * @return StatusTransitionModelInterface[]|GraphTransitionModelInterface[]
     */
    public function getSourceTransitions(): array
    {
        return $this->getCurrentStatus()->getSourceTransitions();
    }

    /**
     * @return StatusTransitionModelInterface[]|GraphTransitionModelInterface[]
     */
    public function getTargetTransitions(): array
    {
        return $this->getCurrentStatus()->getTargetTransitions();
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return StatusTransitionModelInterface[]|GraphTransitionModelInterface[]
     */
    public function getAllowedSourceTransitions(UserInterface $user): array
    {
        return $this->getCurrentStatus()->getAllowedSourceTransitions($user);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return StatusTransitionModelInterface[]|GraphTransitionModelInterface[]
     */
    public function getAllowedTargetTransitions(UserInterface $user): array
    {
        return $this->getCurrentStatus()->getAllowedTargetTransitions($user);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string[]
     */
    public function getAllowedTargetTransitionsCodenames(UserInterface $user): array
    {
        $output = [];

        foreach ($this->getAllowedTargetTransitions($user) as $transition) {
            $output[] = $transition->getCodename();
        }

        return $output;
    }

    /**
     * @return static
     * @throws \BetaKiller\Status\StatusException
     * @todo Move to repo
     */
    public function getStartStatus()
    {
        /** @var StatusModelInterface|null $start */
        $start = $this->statusModelFactory()->getStartNode();

        if (!$start) {
            throw new StatusException('No start status defined for :name', [':name' => \get_class($this)]);
        }

        return $this->setCurrentStatus($start);
    }

    /**
     * @param integer $status_id
     * @param bool    $not_equal
     *
     * @return $this
     * @deprecated
     * @todo Move to repo
     */
    public function filterStatusID($status_id, $not_equal = false)
    {
        $col = $this->object_column($this->getStatusRelationForeignKey());

        return $this->where($col, $not_equal ? '<>' : '=', $status_id);
    }

    /**
     * @param StatusModelInterface $status
     * @param bool                 $not_equal
     *
     * @return $this
     * @deprecated
     * @todo Move to repo
     */
    public function filterStatus(StatusModelInterface $status, $not_equal = false)
    {
        return $this->filterStatusID($status->getID(), $not_equal);
    }

    /**
     * @param array $status_ids
     * @param bool  $not_equal
     *
     * @return $this
     * @deprecated
     * @todo Move to repo
     */
    public function filterStatuses(array $status_ids, $not_equal = false)
    {
        $col = $this->object_column($this->getStatusRelationForeignKey());

        return $this->where($col, $not_equal ? 'NOT IN' : 'IN', $status_ids);
    }

    /**
     * @param \BetaKiller\Acl\Resource\StatusRelatedEntityAclResourceInterface $resource
     * @param string|null                                                      $action
     *
     * @return $this
     * @deprecated
     * @todo Move to repo
     */
    protected function filterAllowedStatuses(StatusRelatedEntityAclResourceInterface $resource, $action = null)
    {
        if (!$action) {
            $action = $resource::ACTION_READ;
        }

        /** @var \BetaKiller\Status\StatusModelInterface[] $allStatuses */
        $allStatuses     = $this->statusModelFactory()->getAllNodes();
        $allowedStatuses = [];

        foreach ($allStatuses as $status) {
            if ($resource->isStatusActionAllowed($status, $action)) {
                $allowedStatuses[] = $status->getID();
            }
        }

        $this->filterStatuses($allowedStatuses);

        return $this;
    }

    public function getStatusID()
    {
        return $this->get($this->getStatusRelationForeignKey());
    }

    public function hasCurrentStatus()
    {
        return (bool)$this->getStatusID();
    }

    /**
     * @param int|array|NULL $id
     *
     * @return StatusModelOrm|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     * @deprecated
     */
    public function statusModelFactory($id = null)
    {
        return ORM::factory($this->getStatusRelationModelName(), $id);
    }

    /**
     * Returns key for workflow factory
     *
     * @return string
     */
    abstract public function getWorkflowName(): string;

    /**
     * @return StatusModelOrm
     */
    protected function getStatusRelation(): StatusModelOrm
    {
        return $this->get($this->getStatusRelationKey());
    }

    /**
     * @param StatusModelInterface $target
     *
     * @return $this
     */
    protected function setCurrentStatus(StatusModelInterface $target)
    {
        return $this->set($this->getStatusRelationKey(), $target);
    }

    protected function getStatusRelationKey(): string
    {
        return 'status';
    }

    /**
     * @return string
     */
    abstract protected function getStatusRelationModelName(): string;

    /**
     * @return string
     */
    abstract protected function getStatusRelationForeignKey(): string;
}
