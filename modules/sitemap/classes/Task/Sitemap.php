<?php

class Task_Sitemap extends Minion_Task
{
    /**
     * @Inject
     * @var \BetaKiller\Service\SitemapService
     */
    private $sitemapService;

    protected function _execute(array $params): void
    {
        $this->sitemapService->generate();
    }
}
