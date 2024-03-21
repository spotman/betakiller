<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\UserState;

class UserStateRepository extends AbstractWorkflowStateOrmRepository implements UserStateRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getUrlKeyName(): string
    {
        return UserState::COL_CODENAME;
    }
}
