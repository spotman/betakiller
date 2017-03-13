<?php
namespace BetaKiller\Helper;

trait RoleModelFactoryTrait
{
    /**
     * @param int|null $id
     * @return \BetaKiller\Model\RoleInterface
     */
    private function model_factory_role($id = null)
    {
        /** @var \BetaKiller\Model\RoleInterface $model */
        $model = \ORM::factory('Role', $id);
        return $model;
    }
}
