<?php
namespace BetaKiller\Status;

interface StatusAclModelInterface
{
    /**
     * Returns array of role`s names
     *
     * @param string $action
     *
     * @return string[]
     */
    public function getActionAllowedRoles($action);

    /**
     * @return \BetaKiller\Model\RoleInterface
     */
    public function getRole();
}
