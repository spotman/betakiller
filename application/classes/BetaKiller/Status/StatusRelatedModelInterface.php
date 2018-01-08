<?php
namespace BetaKiller\Status;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\UserInterface;

interface StatusRelatedModelInterface extends AbstractEntityInterface
{
    public function getWorkflowName(): string;

    /**
     * @return StatusModelInterface
     */
    public function getCurrentStatus();

    /**
     * @param \BetaKiller\Status\StatusModelInterface $target
     *
     * @return $this
     * @throws \BetaKiller\Status\StatusException
     */
    public function changeStatus(StatusModelInterface $target);

    /**
     * @param \BetaKiller\Status\StatusTransitionModelInterface $transition
     *
     * @throws \BetaKiller\Status\StatusException
     */
    public function doStatusTransition(StatusTransitionModelInterface $transition): void;

    /**
     * @param string $codename
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function isStatusTransitionAllowed(string $codename, UserInterface $user): bool;

    /**
     * @return StatusTransitionModelInterface[]
     */
    public function getSourceTransitions();

    /**
     * @return StatusTransitionModelInterface[]
     */
    public function getTargetTransitions();

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return StatusTransitionModelInterface[]
     */
    public function getAllowedSourceTransitions(UserInterface $user);

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return StatusTransitionModelInterface[]
     */
    public function getAllowedTargetTransitions(UserInterface $user);

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string[]
     */
    public function getAllowedTargetTransitionsCodenames(UserInterface $user);

    /**
     * @return $this
     */
    public function getStartStatus();

    /**
     * @param integer $status_id
     * @param bool    $not_equal
     *
     * @return $this
     * @deprecated
     * @todo Move to repo
     */
    public function filterStatusID($status_id, $not_equal = false);

    /**
     * @param StatusModelInterface $status
     * @param bool                 $not_equal
     *
     * @return $this
     * @deprecated
     * @todo Move to repo
     */
    public function filterStatus(StatusModelInterface $status, $not_equal = false);

    /**
     * @param int[] $status_ids
     * @param bool  $not_equal
     *
     * @return $this
     * @deprecated
     * @todo Move to repo
     */
    public function filterStatuses(array $status_ids, $not_equal = false);

    /**
     * @return int
     */
    public function getStatusID();

    /**
     * @return bool
     */
    public function hasCurrentStatus();

    /**
     * @param int|NULL $id
     *
     * @return StatusModelOrm|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    public function statusModelFactory($id = null);
}
