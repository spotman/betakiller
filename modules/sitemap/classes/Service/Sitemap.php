<?php

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Service\AbstractService;
use BetaKiller\Service\ServiceException;
use BetaKiller\Url\AvailableUrlsCollector;
use Psr\Log\LoggerInterface;
use samdark\sitemap\Index;
use samdark\sitemap\Sitemap;

class Service_Sitemap extends AbstractService
{
    /**
     * @var AppConfigInterface
     */
    private $appConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \samdark\sitemap\Sitemap
     */
    private $sitemap;

    /**
     * @var int
     */
    protected $linksCounter;

    /**
     * @var \BetaKiller\Url\AvailableUrlsCollector
     */
    private $urlCollector;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * Service_Sitemap constructor.
     *
     * @param \BetaKiller\Url\AvailableUrlsCollector $urlCollector
     * @param \Psr\Log\LoggerInterface               $logger
     * @param \BetaKiller\Config\AppConfigInterface  $appConfig
     * @param \BetaKiller\Helper\AppEnvInterface     $appEnv
     */
    public function __construct(
        AvailableUrlsCollector $urlCollector,
        LoggerInterface $logger,
        AppConfigInterface $appConfig,
        AppEnvInterface $appEnv
    ) {
        $this->appConfig    = $appConfig;
        $this->logger       = $logger;
        $this->urlCollector = $urlCollector;
        $this->appEnv       = $appEnv;
    }

    /**
     * @return $this
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \InvalidArgumentException
     * @throws \BetaKiller\Service\ServiceException
     */
    public function generate(): self
    {
        $baseUrl = $this->appConfig->getBaseUrl();

        if (strpos($baseUrl, 'http') === false) {
            throw new ServiceException('Please, set "base_url" parameter to full URL (with protocol) in config file init.php');
        }

        // Create sitemap
        $this->sitemap = new Sitemap($this->getSitemapFilePath());

        $items = $this->urlCollector->getPublicAvailableUrls();

        foreach ($items as $item) {
            $url = $item->getUrl();

            $this->logger->debug('Found url :value', [':value' => $url]);

            // Store URL
            $this->sitemap->addItem($url, $item->getLastModified());
            $this->linksCounter++;
        }

        // Write sitemap files
        $this->sitemap->write();

        $sitemapFiles = $this->sitemap->getSitemapUrls($baseUrl);

        if (count($sitemapFiles) > 1) {
            // Create sitemap index file
            $index = new Index($this->getSitemapIndexFilePath());

            // Add URLs
            foreach ($sitemapFiles as $sitemapUrl) {
                $index->addSitemap($sitemapUrl);
            }

            // Write index
            $index->write();
        }

        $this->logger->info(':count links have been written to sitemap.xml', [':count' => $this->linksCounter]);

        return $this;
    }

    public function serve(Response $response)
    {
        $content = file_get_contents($this->getSitemapFilePath());
        $response->send_string($content, $response::TYPE_XML);
    }

    protected function getSitemapFilePath()
    {
        return $this->getDocumentRootPath().DIRECTORY_SEPARATOR.'sitemap.xml';
    }

    protected function getSitemapIndexFilePath()
    {
        return $this->getDocumentRootPath().DIRECTORY_SEPARATOR.'sitemap_index.xml';
    }

    protected function getDocumentRootPath()
    {
        return $this->appEnv->getDocRootPath();
    }
}
