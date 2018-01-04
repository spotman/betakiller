<?php

$container = \BetaKiller\DI\Container::getInstance();

/** @var \BetaKiller\Log\KohanaLogProxy $log */
$log = $container->get(BetaKiller\Log\KohanaLogProxy::class);

// Proxy old Kohana logs to new logging subsystem
\Kohana::$log->attach($log);
