<?php

$errorsDbDsn = 'sqlite:'.APPPATH.'logs'.DIRECTORY_SEPARATOR.'notifications.sqlite';

return [
    'notifications' => [
        'type'         => 'SQLite',
        'connection'   => [
            /**
             * The following options are available for MySQL:
             *
             * string   hostname     server hostname, or socket
             * string   database     database name
             * string   username     database username
             * string   password     database password
             * boolean  persistent   use persistent connections?
             * array    variables    system variables as "key => value" pairs
             *
             * Ports and sockets may be appended to the hostname.
             */
            'dsn'        => $errorsDbDsn,
            'username'   => null,
            'password'   => null,
            'persistent' => false,
        ],
        'table_prefix' => '',
//		'charset'      => 'utf8',
        'caching'      => false,
        'profiling'    => false,
    ],
];
