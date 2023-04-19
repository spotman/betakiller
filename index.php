<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $docRoot  = $_SERVER['DOCUMENT_ROOT'];
    $fileName = $_SERVER['PHP_SELF'];

    // Serve existing files directly
    if (!str_contains($fileName, 'index.php') && \file_exists($docRoot.$fileName)) {
        return false;
    }
}

try {
    $container = include __DIR__.'/bootstrap.php';
} catch (Throwable $e) {
    fallbackExceptionHandler($e);
    exit;
}

runApp($container);
