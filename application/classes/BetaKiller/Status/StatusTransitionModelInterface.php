<?php
namespace BetaKiller\Status;

use BetaKiller\Graph\GraphTransitionModelInterface;
use BetaKiller\Model\RoleInterface;

interface StatusTransitionModelInterface extends GraphTransitionModelInterface
{
    /**
     * @return $this
     */
    public function filter_allowed_by_acl();

    /**
     * Returns iterator for all related roles
     *
     * @return RoleInterface[]
     */
    public function find_all_roles();

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return $this
     */
    public function add_role(RoleInterface $role);

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return $this
     */
    public function remove_role(RoleInterface $role);
}
