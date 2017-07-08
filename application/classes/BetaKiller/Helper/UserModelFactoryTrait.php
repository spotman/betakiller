<?php
namespace BetaKiller\Helper;

/**
 * Trait UserModelFactoryTrait
 *
 * @package BetaKiller\Helper
 * @deprecated
 */
trait UserModelFactoryTrait
{
    /**
     * @param int|null $id
     * @return \BetaKiller\Model\UserInterface|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     * @deprecated Use UserRepository instead
     */
    private function model_factory_user($id = null)
    {
        return \ORM::factory('User', $id);
    }
}
