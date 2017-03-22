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
        $log = new Log_PhpExceptionStorage();
        Kohana::$log->attach($log, Log::NOTICE, Log::EMERGENCY);
    } catch (Exception $e) {
        // Skip this log writer silently
    }
}
