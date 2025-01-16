<?php

$devHost = $_SERVER['HTTP_HOST'] ?? null;

$devUrl = $devHost
    ? 'https://'.$devHost
    : null;

return [
    'url' => [
        'base' => $devUrl ?? getenv('APP_URL'),
    ],
];
