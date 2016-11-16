<?php defined('SYSPATH') OR die('No direct access allowed.');

$ms = MultiSite::instance();

if (!$ms->is_site_detected())
{
    throw new Exception('Migrations task must be called from per-site directory');
}

return [
    /**
     * Scopes with paths for creating migration files
     */
    'scopes'    =>  [
        'core'          =>  APPPATH,
        'core:module'   =>  MODPATH,

        'app'           =>  $ms->site_path(),
        'app:module'    =>  $ms->site_path().DIRECTORY_SEPARATOR.'modules',
    ],
];
