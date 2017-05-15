<?php
namespace BetaKiller\Status;

use BetaKiller\Graph\GraphNodeModelInterface;

interface StatusModelInterface extends GraphNodeModelInterface
{
    /**
     * @return int
     */
    public function get_related_count();

    /**
     * @return StatusRelatedModelInterface[]
     */
    public function get_related_list();

    /**
     * Returns list of transitions allowed by ACL for current user
     *
     * @param \BetaKiller\Graph\GraphNodeModelInterface $source
     * @param \BetaKiller\Graph\GraphNodeModelInterface $target
     *
     * @return \BetaKiller\Graph\GraphTransitionModelInterface[]
     */
    public function get_allowed_transitions(GraphNodeModelInterface $source = null, GraphNodeModelInterface $target = null);

    /**
     * Returns list of source transitions allowed by ACL for current user
     *
     * @return \BetaKiller\Graph\GraphTransitionModelInterface[]
     */
    public function get_allowed_source_transitions();

    /**
     * Returns list of target transitions allowed by ACL for current user
     *
     * @return \BetaKiller\Graph\GraphTransitionModelInterface[]
     */
    public function get_allowed_target_transitions();

    /**
     * Array with codenames of target transitions, <status codename> => <transition codename>
     *
     * @return string[]
     */
    public function get_allowed_target_transitions_codename_array();

    /**
     * Returns TRUE if target transition is allowed
     *
     * @param string $codename
     *
     * @return bool
     */
    public function is_target_transition_allowed($codename);

    /**
     * @param string $action
     *
     * @return string[]
     */
    public function getStatusActionAllowedRoles($action);

    /**
     * Returns true if status-based ACL is enabled (needs *StatusAcl model + *_status_acl table)
     *
     * @return bool
     */
    public function isStatusAclEnabled();
}
