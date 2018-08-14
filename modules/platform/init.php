<?php
declare(strict_types=1);

use BetaKiller\Config\KohanaConfigProvider;
use BetaKiller\Helper\AppEnv;

// Detect site path
$ms = \MultiSite::instance();

if ($ms->isInitialized()) {
    die('MultiSite must not be initialized before platform init');
}

// Import .env and validate env variables
$appEnv = new AppEnv(
    $ms->getWorkingPath(),
    $ms->docRoot(),
    !$ms->isSiteDetected()
);

// Initialize per-site configs, modules, site init.php, etc
$ms->init();

$configProvider = new KohanaConfigProvider;

// Create container instance
$container = \BetaKiller\DI\Container::getInstance();

// Initialize container and push AppEnv and ConfigProvider into DIC
$container->init($configProvider, $appEnv);
