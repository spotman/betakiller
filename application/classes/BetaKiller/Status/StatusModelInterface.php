<?php
namespace BetaKiller\Status;

use BetaKiller\Graph\GraphNodeModelInterface;
use BetaKiller\Model\UserInterface;

interface StatusModelInterface extends GraphNodeModelInterface
{
    /**
     * @return int
     */
    public function get_related_count(): int;

    /**
     * @return StatusRelatedModelInterface[]
     */
    public function get_related_list(): array;

    /**
     * Returns list of transitions allowed by ACL for current user
     *
     * @param \BetaKiller\Model\UserInterface $user
     * @param \BetaKiller\Graph\GraphNodeModelInterface $source
     * @param \BetaKiller\Graph\GraphNodeModelInterface $target
     *
     * @return \BetaKiller\Graph\GraphTransitionModelInterface[]
     */
    public function get_allowed_transitions(UserInterface $user, GraphNodeModelInterface $source = null, GraphNodeModelInterface $target = null): array;

    /**
     * Returns list of source transitions allowed by ACL for current user
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Graph\GraphTransitionModelInterface[]
     */
    public function get_allowed_source_transitions(UserInterface $user): array;

    /**
     * Returns list of target transitions allowed by ACL for current user
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Graph\GraphTransitionModelInterface[]
     */
    public function get_allowed_target_transitions(UserInterface $user): array;

    /**
     * Array with codenames of target transitions, <status codename> => <transition codename>
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string[]
     */
    public function get_allowed_target_transitions_codename_array(UserInterface $user): array;

    /**
     * Returns TRUE if target transition is allowed
     *
     * @param string $codename
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function is_target_transition_allowed(string $codename, UserInterface $user): bool;

    /**
     * @param string $action
     *
     * @return string[]
     */
    public function getStatusActionAllowedRoles(string $action): array;

    /**
     * Returns true if status-based ACL is enabled (needs *StatusAcl model + *_status_acl table)
     *
     * @return bool
     */
    public function isStatusAclEnabled(): bool;
}
