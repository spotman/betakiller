<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\AppRunnerInterface;

interface AppRunnerFactoryInterface
{
    public function create(): AppRunnerInterface;
}
