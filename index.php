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

$container = include __DIR__.'/bootstrap.php';

runApp($container);
