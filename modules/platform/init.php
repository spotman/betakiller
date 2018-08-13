<?php
declare(strict_types=1);

// Patch for correct "base_url" initialization logic (platform is loaded before multi-site and inject env variables)
// Creating instance will fetch and validate env variables from .env
\BetaKiller\DI\Container::getInstance()->get(BetaKiller\Helper\AppEnvInterface::class);
