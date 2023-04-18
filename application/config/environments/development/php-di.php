<?php

use Beberlei\Metrics\Collector\Collector;
use Beberlei\Metrics\Collector\StatsD;
use function DI\factory;

return [

    'definitions' => [

        Collector::class => factory(function () {
            // Send metrics to the local StatsD instance
            $host = getenv('STATSD_HOST');
            $port = getenv('STATSD_PORT');

            return new StatsD($host, $port);
        }),

    ],

];
