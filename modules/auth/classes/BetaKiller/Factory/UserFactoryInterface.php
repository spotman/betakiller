<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Model\UserInterface;

interface UserFactoryInterface
{
    public function create(UserInfo $info): UserInterface;
}
