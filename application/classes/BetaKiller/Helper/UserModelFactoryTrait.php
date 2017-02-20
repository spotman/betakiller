<?php
namespace BetaKiller\Helper;

trait UserModelFactoryTrait
{
    /**
     * @param int|null $id
     * @return \BetaKiller\Model\UserInterface|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    private function model_factory_user($id = null)
    {
        return \ORM::factory('User', $id);
    }
}
