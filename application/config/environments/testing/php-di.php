<?php

use Beberlei\Metrics\Collector\Collector;
use Beberlei\Metrics\Collector\NullCollector;
use function DI\factory;

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'compile' => false,

    'definitions' => [

        Collector::class => factory(function () {
            // Send metrics to /dev/null
            return new NullCollector();
        }),

    ],

];
