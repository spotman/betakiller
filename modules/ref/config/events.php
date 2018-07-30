<?php
declare(strict_types=1);

return [
    \BetaKiller\Event\UrlDispatchedEvent::class => [
        \BetaKiller\Ref\RefUrlDispatchedEventHandler::class,
    ],
];
