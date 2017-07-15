<?php defined('SYSPATH') OR die('No direct script access.');

class Task_Sitemap extends Minion_Task
{
    /**
     * @Inject
     * @var \Service_Sitemap
     */
    private $sitemapService;

    protected function _execute(array $params): void
    {
        $this->sitemapService->generate();
    }
}
