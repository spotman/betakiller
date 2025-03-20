<?php

declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Model\UserSession;
use BetaKiller\Model\UserSessionInterface;

final readonly class UserSessionFactory implements UserSessionFactoryInterface
{
    public function create(string $id): UserSessionInterface
    {
        $model = new UserSession();

        $model
            ->setToken($id)
            ->setCreatedAt(new \DateTimeImmutable());

        return $model;
    }
}
