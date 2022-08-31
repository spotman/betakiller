<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\UserInterface;

interface CreatedByAtRepositoryInterface extends CreatedAtRepositoryInterface
{
    public function migrateBetweenUsers(UserInterface $from, UserInterface $to): void;
}
