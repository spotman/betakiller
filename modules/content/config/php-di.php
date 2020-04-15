<?php
declare(strict_types=1);

use BetaKiller\Repository\ContentCommentRepository;
use BetaKiller\Repository\ContentCommentRepositoryInterface;
use BetaKiller\Repository\ContentCommentStateRepository;
use BetaKiller\Repository\ContentCommentStateRepositoryInterface;
use BetaKiller\Repository\ContentPostRepository;
use BetaKiller\Repository\ContentPostRepositoryInterface;
use BetaKiller\Repository\ContentPostStateRepository;
use BetaKiller\Repository\ContentPostStateRepositoryInterface;
use function DI\autowire;

return [
    'definitions' => [
        ContentCommentRepositoryInterface::class      => autowire(ContentCommentRepository::class),
        ContentCommentStateRepositoryInterface::class => autowire(ContentCommentStateRepository::class),
        ContentPostRepositoryInterface::class         => autowire(ContentPostRepository::class),
        ContentPostStateRepositoryInterface::class    => autowire(ContentPostStateRepository::class),
    ],
];
