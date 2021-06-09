<?php

use Beberlei\Metrics\Collector\Collector;
use Beberlei\Metrics\Collector\Logger;
use Psr\Log\LoggerInterface;
use function DI\factory;

return [

    /**
     * @url http://php-di.org/doc/performances.html
     */
    'compile' => false,

    'definitions' => [

        Collector::class => factory(function (LoggerInterface $logger) {
            // Send metrics to Monolog
            return new Logger($logger);
        }),

    ],

];
