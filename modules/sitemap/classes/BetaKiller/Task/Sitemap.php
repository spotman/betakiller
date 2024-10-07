<?php

declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Service\SitemapService;

class Sitemap extends AbstractTask
{
    /**
     * @var \BetaKiller\Service\SitemapService
     */
    private $service;

    /**
     * Sitemap constructor.
     *
     * @param \BetaKiller\Service\SitemapService $service
     */
    public function __construct(SitemapService $service)
    {
        $this->service = $service;
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        // No options
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $this->service->generate();
    }
}
