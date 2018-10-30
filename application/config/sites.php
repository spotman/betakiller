<?php

return [

    // Prevent automatic MultiSite initialization, it will be processed in /modules/platform/init.php explicitly
    'init' => false,

    'path' => __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..',

    // Disable putting Kohana logs to site-related logs folder
    'logs' => false,

];
