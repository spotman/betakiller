<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @author Spotman i.am@spotman.ru
 *
 * Error`s console
 */

/**
 * Attach the SQLite log-service to logging. Multiple writers are supported.
 */
// TODO Remove this in favour of custom Monolog handler
if (Kohana::in_production(true)) {
    try {
        /** @var Log_PhpExceptionStorage $log */
        $log = \BetaKiller\DI\Container::getInstance()->get(Log_PhpExceptionStorage::class);
        $log->register();
    } catch (Throwable $e) {
        // Skip this log writer silently
    }
}
