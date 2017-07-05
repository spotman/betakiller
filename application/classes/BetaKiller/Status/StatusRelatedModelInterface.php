<?php
namespace BetaKiller\Status;

use BetaKiller\Model\AbstractEntityInterface;

interface StatusRelatedModelInterface extends AbstractEntityInterface
{
    /**
     * @return StatusModelInterface
     */
    public function get_current_status();

    /**
     * @param \BetaKiller\Status\StatusModelInterface $target
     *
     * @return $this
     * @throws \BetaKiller\Status\StatusException
     */
    public function change_status(StatusModelInterface $target);

    /**
     * @param \BetaKiller\Status\StatusTransitionModelInterface $transition
     *
     * @return $this
     * @throws \BetaKiller\Status\StatusException
     */
    public function do_status_transition(StatusTransitionModelInterface $transition);

    /**
     * @param string $codename
     *
     * @return bool
     */
    public function is_status_transition_allowed($codename);

    /**
     * @return StatusTransitionModelInterface[]
     */
    public function get_source_transitions();

    /**
     * @return StatusTransitionModelInterface[]
     */
    public function get_target_transitions();

    /**
     * @return StatusTransitionModelInterface[]
     */
    public function get_allowed_source_transitions();

    /**
     * @return StatusTransitionModelInterface[]
     */
    public function get_allowed_target_transitions();

    /**
     * @return string[]
     */
    public function get_allowed_target_transitions_codenames();

    /**
     * @return $this
     */
    public function set_start_status();

    /**
     * @param integer $status_id
     * @param bool    $not_equal
     *
     * @return $this
     * @deprecated
     * @todo Move to repo
     */
    public function filter_status_id($status_id, $not_equal = false);

    /**
     * @param StatusModelInterface $status
     * @param bool                 $not_equal
     *
     * @return $this
     * @deprecated
     * @todo Move to repo
     */
    public function filter_status(StatusModelInterface $status, $not_equal = false);

    /**
     * @param int[] $status_ids
     * @param bool  $not_equal
     *
     * @return $this
     * @deprecated
     * @todo Move to repo
     */
    public function filter_statuses(array $status_ids, $not_equal = false);

    /**
     * @return int
     */
    public function get_status_id();

    /**
     * @return bool
     */
    public function has_current_status();

    /**
     * @param int|NULL $id
     *
     * @return StatusModelOrm|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    public function status_model_factory($id = null);
}
