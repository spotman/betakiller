<?php
declare(strict_types=1);

use BetaKiller\Config\KohanaConfigProvider;
use BetaKiller\Helper\AppEnv;

// Detect site path
$ms = \MultiSite::instance();

if ($ms->isInitialized()) {
    throw new \LogicException('MultiSite must not be initialized before platform init');
}

// Import .env and validate env variables
$appEnv = new AppEnv(
    $ms->getWorkingPath(),
    $ms->docRoot(),
    !$ms->isSiteDetected()
);

// Initialize logger
$logger = new \BetaKiller\Log\Logger($appEnv);

// Proxy old Kohana logs to new logging subsystem
\Kohana::$log->attach(new \BetaKiller\Log\KohanaLogProxy($logger));

// Initialize per-site config directories, modules, site init.php, etc
$ms->init();

// Instantiate config provider
$configProvider = new KohanaConfigProvider;

// Create container instance
$container = \BetaKiller\DI\Container::getInstance();

// Initialize container and push AppEnv and ConfigProvider into DIC
$container->init($configProvider, $appEnv, $logger);

if ($appEnv->isDebugEnabled()) {
    $logger->debug('Running :name env', [':name' => $this->appEnv->getModeName()]);
}
