<?php
declare(strict_types=1);

use BetaKiller\Event\MissingUrlEvent;
use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\HitStat\HitStatMissingUrlEventHandler;
use BetaKiller\HitStat\HitStatUrlDispatchedEventHandler;

return [
    UrlDispatchedEvent::class => [
        HitStatUrlDispatchedEventHandler::class,
    ],

    MissingUrlEvent::class => [
        HitStatMissingUrlEventHandler::class
    ]
];
