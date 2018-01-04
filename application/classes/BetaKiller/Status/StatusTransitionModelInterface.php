<?php
namespace BetaKiller\Status;

use BetaKiller\Graph\GraphTransitionModelInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;

interface StatusTransitionModelInterface extends GraphTransitionModelInterface
{
    /**
     * @param \BetaKiller\Model\UserInterface $user
     * @return $this
     */
    public function filter_allowed_by_acl(UserInterface $user);

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

    /**
     * @return string[]
     */
    public function getTransitionAllowedRolesNames();
}
