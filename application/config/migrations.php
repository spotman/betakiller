<?php

use BetaKiller\Env\AppEnv;

$scopes = [
    'core'        => APPPATH,
    'core:module' => MODPATH,
];

$appEnv = AppEnv::instance();

if ($appEnv->isAppRunning()) {
    $scopes = array_merge($scopes, [
        'app'        => $appEnv->getAppRootPath(),
        'app:module' => $appEnv->getAppRootPath().DIRECTORY_SEPARATOR.'modules',
    ]);
}

return [
    /**
     * Scopes with paths for creating migration files
     */
    'scopes' => $scopes,
];
