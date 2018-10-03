<?php

class Controller_Sitemap extends Controller
{
    /**
     * @Inject
     * @var \BetaKiller\Service\SitemapService
     */
    private $sitemapService;

    public function action_index(): void
    {
        $this->sitemapService->generate()->serve($this->response);
    }
}
