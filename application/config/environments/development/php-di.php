<?php

use Beberlei\Metrics\Collector\Collector;
use Beberlei\Metrics\Collector\NullCollector;
use function DI\factory;

return [

    'definitions' => [

        Collector::class => factory(function () {
            return new NullCollector();
        }),

    ],

];
