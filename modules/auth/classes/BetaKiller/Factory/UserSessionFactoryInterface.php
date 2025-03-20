<?php

declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Model\UserSessionInterface;

interface UserSessionFactoryInterface
{
    public function create(string $id): UserSessionInterface;
}
