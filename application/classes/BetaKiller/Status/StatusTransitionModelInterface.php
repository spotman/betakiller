<?php
namespace BetaKiller\Workflow;

use BetaKiller\Graph\GraphTransitionModelInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;

/**
 * Interface StatusTransitionModelInterface
 *
 * @package BetaKiller\Status
 * @deprecated
 */
interface StatusTransitionModelInterface extends GraphTransitionModelInterface
{
    /**
     * @param \BetaKiller\Model\UserInterface $user
     * @return $this
     * @deprecated
     */
    public function filterAllowedByAcl(UserInterface $user);

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return $this
     * @deprecated
     */
    public function addRole(RoleInterface $role);

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return $this
     * @deprecated
     */
    public function removeRole(RoleInterface $role);

    /**
     * @return string[]
     * @deprecated
     */
    public function getTransitionAllowedRolesNames();
}
