<?php

$maintenanceDbDsn = 'sqlite:'.__DIR__.DIRECTORY_SEPARATOR.'maintenance.sqlite';

return [
    'maintenance' => [
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
            'dsn'        => $maintenanceDbDsn,
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
