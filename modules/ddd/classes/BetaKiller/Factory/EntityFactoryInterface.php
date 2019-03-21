<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Model\AbstractEntityInterface;

interface EntityFactoryInterface
{
    public function create(string $name): AbstractEntityInterface;
}
