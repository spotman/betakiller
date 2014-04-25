<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @author Spotman denis.terehov@tsap-spb.ru
 *
 * Сервис для автоматизированного сбора ошибок во фронтенде)
 */

// Отлавливаем возникающие в продакшне JavaScript ошибки
if ( Kohana::$environment == Kohana::PRODUCTION )
{
    // JS::instance()->add_static('error/js-error-catcher.js');
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
