<?php

use BetaKiller\Auth\DefaultUserUrlDetector;
use BetaKiller\Auth\UserUrlDetectorInterface;

return [

    'definitions' => [
        UserUrlDetectorInterface::class => \DI\autowire(DefaultUserUrlDetector::class),
    ],

];
