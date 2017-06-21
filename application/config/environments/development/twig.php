<?php defined('SYSPATH') OR die('No direct script access.');

return array(

	'environment' => array(
	    // Dev stations may have different users for CLI and HTTP server, so group access is required
        'chmod'               => 0775,
	),
);
