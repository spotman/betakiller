<?php

use BetaKiller\Session\DatabaseSessionStorage;
use BetaKiller\Session\SessionStorageInterface;

return [

    'definitions' => [

        SessionStorageInterface::class => DI\autowire(DatabaseSessionStorage::class),

    ],

];
