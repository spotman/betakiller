<?php
declare(strict_types=1);

namespace BetaKiller\Test;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\User;
use BetaKiller\Task\AbstractTask;

final class OrmTest extends AbstractTestCase
{
    public function testUserConstructor(): void
    {
        $object = \ORM::factory(User::getModelName());

        self::assertInstanceOf(ExtendedOrmInterface::class, $object);
    }

    public function testFetchUserRolesCache(): void
    {
        $orm = \ORM::factory(User::getModelName());

        /** @var User $user */
        $user = $orm->where(User::COL_USERNAME, '=', AbstractTask::CLI_USER_NAME)->find();

        \Database_Query::resetQueryCount();

        // Get roles 3 times, they must be loaded from DB only once
        $user->getRoles();

        self::assertEquals(1, \Database_Query::getQueryCount());
    }
}
