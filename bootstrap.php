<?php

declare(strict_types=1);

try {
    include_once __DIR__.'/functions.php';

    return bootstrapPlatform();
} catch (Throwable $e) {
    fallbackExceptionHandler($e);
}
