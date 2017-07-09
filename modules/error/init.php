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
        /** @var Log_PhpExceptionService $log */
        $log = \BetaKiller\DI\Container::getInstance()->get(Log_PhpExceptionService::class);
        $log->register();
    } catch (Throwable $ignored) {
        // Skip this log writer silently
    }
}
