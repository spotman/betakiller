<?php

use BetaKiller\Monitoring\MetricsCollectorInterface;
use BetaKiller\Monitoring\NoOpMetricsCollector;

use function DI\factory;

return [

    'definitions' => [

        MetricsCollectorInterface::class => factory(function () {
            // Send metrics to /dev/null
            return new NoOpMetricsCollector();
        }),

    ],

];
