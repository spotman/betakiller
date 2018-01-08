<?php

$errorsDbDsn = 'sqlite:'.implode(DIRECTORY_SEPARATOR, [realpath(MODPATH.'error'), 'media', 'errors.sqlite']);

return array
(
	'filesystem' => array
	(
		'type'       => 'SQLite',
		'connection' => array(
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
            'dsn' => $errorsDbDsn,
            'username'   => null,
            'password'   => null,
            'persistent' => false,
        ),
		'table_prefix' => '',
//		'charset'      => 'utf8',
		'caching'      => false,
		'profiling'    => false,
	),
);
