<?php

return [
    'default' => [
        'type'         => 'MySQLi',
        'connection'   => [
            /**
             * The following options are available for MySQL:
             *
             * string   hostname     server hostname, or socket
             * string   database     db name
             * string   username     db username
             * string   password     db password
             * boolean  persistent   use persistent connections?
             * array    variables    system variables as "key => value" pairs
             *
             * Ports and sockets may be appended to the hostname.
             */
            'hostname'   => getenv('MYSQL_HOST'),
            'database'   => getenv('MYSQL_DB'),
            'username'   => getenv('MYSQL_USER'),
            'password'   => getenv('MYSQL_PASS'),
            'port'       => getenv('MYSQL_PORT'),
            'persistent' => false,
        ],
        'table_prefix' => '',
        'charset'      => 'utf8mb4',
        'caching'      => !empty(getenv('APP_CACHE')),
    ],
];
