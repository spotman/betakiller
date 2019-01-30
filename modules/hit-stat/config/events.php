<?php
declare(strict_types=1);

return [
    \BetaKiller\Event\UrlDispatchedEvent::class => [
        \BetaKiller\HitStat\HitStatUrlDispatchedEventHandler::class,
    ],

    \BetaKiller\Event\MissingUrlEvent::class => [
        \BetaKiller\HitStat\HitStatMissingUrlEventHandler::class
    ]
];
