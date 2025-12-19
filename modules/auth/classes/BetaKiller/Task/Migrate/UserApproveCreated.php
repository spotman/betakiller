<?php

declare(strict_types=1);

namespace BetaKiller\Task\Migrate;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleTaskInterface;
use BetaKiller\Exception\LogicException;
use BetaKiller\Factory\EntityFactoryInterface;
use BetaKiller\Model\User;
use BetaKiller\Model\UserState;
use BetaKiller\Repository\UserRepositoryInterface;
use Database;
use DB;
use ORM;
use Psr\Log\LoggerInterface;

/**
 * Approve all Users in "created" UserState (migration for auto-approval)
 */
final readonly class UserApproveCreated implements ConsoleTaskInterface
{
    private const ARG_FORCE = 'force';

    public function __construct(
        private EntityFactoryInterface $entityFactory,
        private LoggerInterface $logger
    ) {
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder
                ->bool(self::ARG_FORCE)
                ->optional(false)
                ->label('Proceed even if auto-approve is disabled (risky)'),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $userEntity = $this->entityFactory->create(User::getModelName());

        if (!$userEntity instanceof User) {
            throw new LogicException();
        }

        if (!$params->getBool(self::ARG_FORCE) && !$userEntity::isAutoApproveEnabled()) {
            throw new LogicException('Auto-approve is not enabled, use "force" option to proceed anyway');
        }

        $stateEntity = $this->entityFactory->create($userEntity::getWorkflowStateModelName());

        if (!$stateEntity instanceof UserState) {
            throw new LogicException();
        }

        $usersAlias    = 'u';
        $usersTable    = DB::expr($userEntity->table_name());
        $usersId       = DB::expr(ORM::col($usersAlias, $userEntity->primary_key()));
        $usersStatusId = DB::expr(ORM::col($usersAlias, $userEntity::getWorkflowStateForeignKey()));

        $statesAlias    = 's';
        $statesTable    = DB::expr($stateEntity->table_name());
        $statesId       = DB::expr(ORM::col($statesAlias, $stateEntity->primary_key()));
        $statesCodename = DB::expr(ORM::col($statesAlias, $stateEntity::COL_CODENAME));

        $stateCodenameCreated  = UserState::CREATED;
        $stateCodenameApproved = UserState::APPROVED;

        $count = DB::query(
            Database::UPDATE,
            "UPDATE :users_table AS u
LEFT JOIN :states_table AS s ON (:states_id = :users_status_id)
SET :users_status_id = (SELECT :states_id FROM :states_table AS s WHERE :states_codename = :state_approved)
WHERE :states_codename = :state_created;"
        )
//            ->bind(':users_alias', $usersAlias)
            ->bind(':users_table', $usersTable)
            ->bind(':users_id', $usersId)
            ->bind(':users_status_id', $usersStatusId)
            //
//            ->bind(':states_alias', $statesAlias)
            ->bind(':states_table', $statesTable)
            ->bind(':states_id', $statesId)
            ->bind(':states_codename', $statesCodename)
            //
            ->bind(':state_created', $stateCodenameCreated)
            ->bind(':state_approved', $stateCodenameApproved)
            ->execute();

        $this->logger->info(':count Users updated', [
            ':count' => $count,
        ]);
    }
}
