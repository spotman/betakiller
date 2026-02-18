<?php

declare(strict_types=1);

use BetaKiller\Event\MissingUrlEvent;
use BetaKiller\HitStat\HitStatMissingUrlEventHandler;

return [
    MissingUrlEvent::class => [
        HitStatMissingUrlEventHandler::class,
    ],
];
