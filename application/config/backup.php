<?php

use BetaKiller\Env\AppEnv;

return [

    'database' => 'default',
    'folder'   => AppEnv::instance()->getAppRootPath(),

];
