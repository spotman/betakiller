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
    public function filterAllowedByAcl(UserInterface $user);

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return $this
     */
    public function addRole(RoleInterface $role);

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return $this
     */
    public function removeRole(RoleInterface $role);

    /**
     * @return string[]
     */
    public function getTransitionAllowedRolesNames();
}
