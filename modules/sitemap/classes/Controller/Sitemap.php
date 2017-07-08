<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Sitemap extends Controller
{
    /**
     * @Inject
     * @var \Service_Sitemap
     */
    private $sitemapService;

    public function action_index(): void
    {
        $this->sitemapService->generate()->serve($this->getResponse());
    }
}
