<?php

use BetaKiller\CliAppRunnerInterface;
use BetaKiller\Console\ConsoleOptionBuilder;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleTaskLocator;
use BetaKiller\Console\ConsoleTaskLocatorInterface;
use BetaKiller\ConsoleAppRunner;

use function DI\autowire;

return [

    'definitions' => [
        CliAppRunnerInterface::class => autowire(ConsoleAppRunner::class),

        ConsoleOptionBuilderInterface::class => autowire(ConsoleOptionBuilder::class),
        ConsoleTaskLocatorInterface::class   => autowire(ConsoleTaskLocator::class),
    ],

];
