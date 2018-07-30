<?php
declare(strict_types=1);

use BetaKiller\Event\MissingUrlEvent;
use BetaKiller\MissingUrl\MissingUrlEventHandler;

return [
    MissingUrlEvent::class => [
        MissingUrlEventHandler::class
    ]
];
