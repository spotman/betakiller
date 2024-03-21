<?php

use BetaKiller\Auth\DefaultUserUrlDetector;
use BetaKiller\Auth\HmacPasswordHasher;
use BetaKiller\Auth\PasswordHasherInterface;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Config\SessionConfig;
use BetaKiller\Config\SessionConfigInterface;
use BetaKiller\Factory\UserFactory;
use BetaKiller\Factory\UserFactoryInterface;
use function DI\autowire;

return [

    'definitions' => [
        SessionConfigInterface::class   => autowire(SessionConfig::class),
        UserUrlDetectorInterface::class => autowire(DefaultUserUrlDetector::class),
        UserFactoryInterface::class     => autowire(UserFactory::class),
        PasswordHasherInterface::class  => autowire(HmacPasswordHasher::class),
    ],

];
