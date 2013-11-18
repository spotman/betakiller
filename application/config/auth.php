<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(
	'driver'       => 'ORM',
	'hash_method'  => 'sha256',

    // DO NOT CHANGE THIS - ALL PASSWORD HASHES in DB WOULD BECOME INCORRECT
	'hash_key'     => 'the_secret_hash_key_which_nobody_can_hack',
	'lifetime'     => 1209600,
	'session_type' => Session::$default,
	'session_key'  => 'auth_user',
);
