<?php
declare(strict_types=1);

namespace BetaKiller\Task;

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

        parent::__construct();
    }

    public function defineOptions(): array
    {
        // No options
        return [];
    }

    public function run(): void
    {
        $this->service->generate();
    }
}
