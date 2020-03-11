<?php
declare(strict_types=1);

use BetaKiller\Error\ErrorPageRenderer;
use BetaKiller\Error\ErrorPageRendererInterface;
use BetaKiller\Repository\PhpExceptionRepository;
use BetaKiller\Repository\PhpExceptionRepositoryInterface;
use function DI\autowire;

return [
    'definitions' => [
        ErrorPageRendererInterface::class      => autowire(ErrorPageRenderer::class)->lazy(),
        PhpExceptionRepositoryInterface::class => autowire(PhpExceptionRepository::class),
    ],
];
