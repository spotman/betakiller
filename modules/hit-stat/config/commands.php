<?php
declare(strict_types=1);

use BetaKiller\Command\HitStatStoreCommand;
use BetaKiller\CommandHandler\HitStatStoreCommandHandler;

return [
    HitStatStoreCommand::class => HitStatStoreCommandHandler::class,
];
