<?php
declare(strict_types=1);

use BetaKiller\Env\AppEnv;
use BetaKiller\Env\AppEnvInterface;

include_once __DIR__.'/functions.php';

configureKohana();

$appEnv = AppEnv::instance();

$envMode = $appEnv->isAppRunning()
    ? $appEnv->getModeName()
    : AppEnvInterface::MODE_DEVELOPMENT;

bootstrapKohana($envMode);
return bootstrapApp($appEnv);
