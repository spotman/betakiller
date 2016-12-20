<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @author Spotman i.am@spotman.ru
 *
 * Error`s console
 */

/**
 * Attach the MongoDB log-service to logging. Multiple writers are supported.
 */
if ( Kohana::in_production(TRUE) )
{
    try {
        $log = new Log_MongoDB;
        Kohana::$log->attach($log, Log::NOTICE, Log::EMERGENCY);
    } catch (Exception $e) {
        // Skip this log writer silently
    }
}

Route::set('catch-js-error', 'catch-js-error')
    ->defaults(array(
        'module'        => 'error',
        'directory'     => 'Error',
        'controller'    => 'Js',
        'action'        => 'catch',
    ));

Route::set('error-php-list', 'errors/php')
    ->defaults(array(
        'module'        => 'error',
        'directory'     => 'Error',
        'controller'    => 'Php',
        'action'        => 'index',
    ));

Route::set('error-php-actions', 'errors/php/action/<action>(/<param>)')
    ->defaults(array(
        'module'        => 'error',
        'directory'     => 'Error',
        'controller'    => 'Php',
        'action'        => 'index',
    ));

Route::set('error-php-message', 'errors/php/<hash>(/<action>)')
    ->defaults(array(
        'module'        => 'error',
        'directory'     => 'Error',
        'controller'    => 'PhpMessage',
        'action'        => 'show',
    ));

Route::set('error-widget', 'errors/widget(/<action>)')
    ->defaults(array(
        'module'        => 'error',
        'directory'     => 'Error',
        'controller'    => 'Widget',
        'action'        => 'index',
    ));

