<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Url\AvailableUrlsCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use samdark\sitemap\Index;
use samdark\sitemap\Sitemap;

class SitemapService
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
     * SitemapService constructor.
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
     * @return \BetaKiller\Service\SitemapService
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Service\ServiceException
     */
    public function generate(): self
    {
        $baseUrl = (string)$this->appConfig->getBaseUri();

        if (strpos($baseUrl, 'http') === false) {
            throw new ServiceException('Please, set "base_url" parameter to full URL (with protocol) in config file init.php');
        }

        // Create sitemap
        $this->sitemap = new Sitemap($this->getSitemapFilePath());

        foreach ($this->urlCollector->getPublicAvailableUrls() as $item) {
            $url = $item->getUrl();

            $this->logger->debug('Found url :value', [':value' => $url]);

            // Store URL
            $this->sitemap->addItem($url, $item->getLastModified());
            $this->linksCounter++;
        }

        // Write sitemap files
        $this->sitemap->write();

        $sitemapFiles = $this->sitemap->getSitemapUrls($baseUrl);

        if (\count($sitemapFiles) > 1) {
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

    public function delete(): void
    {
        $path = $this->getSitemapFilePath();

        if (\file_exists($path)) {
            \unlink($path);
        }

        $path = $this->getSitemapFilePath();

        if (\file_exists($path)) {
            \unlink($path);
        }
    }

    public function serve(): ResponseInterface
    {
        $content = file_get_contents($this->getSitemapFilePath());

        return ResponseHelper::xml($content);
    }

    protected function getSitemapFilePath(): string
    {
        return $this->getDocumentRootPath().DIRECTORY_SEPARATOR.'sitemap.xml';
    }

    protected function getSitemapIndexFilePath(): string
    {
        return $this->getDocumentRootPath().DIRECTORY_SEPARATOR.'sitemap_index.xml';
    }

    protected function getDocumentRootPath(): string
    {
        return $this->appEnv->getDocRootPath();
    }
}
