<?php
namespace BetaKiller\Helper;

trait RoleModelFactoryTrait
{
    /**
     * @param int|null $id
     * @return \BetaKiller\Model\RoleInterface|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    private function model_factory_role($id = null)
    {
        return \ORM::factory('Role', $id);
    }
}
