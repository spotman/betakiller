<?php
namespace BetaKiller\Helper;

/**
 * Trait RoleModelFactoryTrait
 *
 * @package BetaKiller\Helper
 * @deprecated
 */
trait RoleModelFactoryTrait
{
    /**
     * @param int|null $id
     * @return \BetaKiller\Model\RoleInterface
     * @deprecated Use RolesRepository instead
     */
    private function model_factory_role($id = null)
    {
        /** @var \BetaKiller\Model\RoleInterface $model */
        $model = \ORM::factory('Role', $id);
        return $model;
    }
}
