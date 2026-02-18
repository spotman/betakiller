<?php

declare(strict_types=1);

use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\EventHandler\UrlDispatchedProceedUtmMarkers;

return [
    UrlDispatchedEvent::class => [
        UrlDispatchedProceedUtmMarkers::class,
    ],
];
