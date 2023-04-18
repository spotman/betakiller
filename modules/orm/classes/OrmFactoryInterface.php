<?php
declare(strict_types=1);

use BetaKiller\Model\ExtendedOrmInterface;

interface OrmFactoryInterface
{
    public function create(string $name): ExtendedOrmInterface;
}
