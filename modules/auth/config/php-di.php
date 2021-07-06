<?php

use BetaKiller\Auth\DefaultUserUrlDetector;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Config\SessionConfig;
use BetaKiller\Config\SessionConfigInterface;
use function DI\autowire;

return [

    'definitions' => [
        SessionConfigInterface::class   => autowire(SessionConfig::class),
        UserUrlDetectorInterface::class => autowire(DefaultUserUrlDetector::class),
    ],

];
