<?php
declare(strict_types=1);

use BetaKiller\Env\AppEnv;

if (PHP_SAPI === 'cli-server') {
    $docRoot  = $_SERVER['DOCUMENT_ROOT'];
    $fileName = $_SERVER['PHP_SELF'];

    // Serve existing files directly
    if (!str_contains($fileName, 'index.php') && \file_exists($docRoot.$fileName)) {
        return false;
    }
}

include_once __DIR__.'/functions.php';

try {
    configureKohana();

    $appEnv = AppEnv::instance();

    $envMode = $appEnv->isAppRunning()
        ? $appEnv->getModeName()
        : AppEnv::MODE_DEVELOPMENT;

    bootstrapKohana($envMode);

//    var_dump($_ENV);
//    var_dump(Kohana::include_paths());
//    var_dump(Kohana::$config->load('database'));
//    die;
    bootstrapApp($appEnv);
} catch (Throwable $e) {
    fallbackExceptionHandler($e);
    exit;
}
