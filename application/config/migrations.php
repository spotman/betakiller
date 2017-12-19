<?php

$ms = MultiSite::instance();

if (!$ms->isSiteDetected()) {
    throw new Exception('Migrations task must be called from per-site directory');
}

return [
    /**
     * Scopes with paths for creating migration files
     */
    'scopes'    =>  [
        'core'          =>  APPPATH,
        'core:module'   =>  MODPATH,

        'app'           =>  $ms->getSitePath(),
        'app:module'    =>  $ms->getSitePath().DIRECTORY_SEPARATOR.'modules',
    ],
];
