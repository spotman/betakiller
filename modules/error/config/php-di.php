<?php

use BetaKiller\Error\PhpExceptionStorageInterface;
use BetaKiller\Error\PhpExceptionStorage;

return [

    'definitions' => [

        PhpExceptionStorageInterface::class => DI\object(PhpExceptionStorage::class),

    ],

];
