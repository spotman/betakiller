<?php

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate;
use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\Service\AbstractService;
use BetaKiller\Service\ServiceException;
use Psr\Log\LoggerInterface;
use samdark\sitemap\Index;
use samdark\sitemap\Sitemap;

class Service_Sitemap extends AbstractService
{
    /**
     * @var UrlContainerInterface
     */
    private $urlParameters;

    /**
     * @var IFaceModelProviderAggregate
     */
    private $ifaceModelProvider;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

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
     * Service_Sitemap constructor.
     *
     * @param \BetaKiller\IFace\Url\UrlContainerInterface                 $urlParameters
     * @param \BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate $ifaceModelProvider
     * @param \BetaKiller\Helper\IFaceHelper                              $ifaceHelper
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \BetaKiller\Config\AppConfigInterface                       $appConfig
     */
    public function __construct(
        UrlContainerInterface $urlParameters,
        IFaceModelProviderAggregate $ifaceModelProvider,
        IFaceHelper $ifaceHelper,
        LoggerInterface $logger,
        AppConfigInterface $appConfig
    ) {
        $this->urlParameters      = $urlParameters;
        $this->ifaceModelProvider = $ifaceModelProvider;
        $this->ifaceHelper        = $ifaceHelper;
        $this->appConfig          = $appConfig;
        $this->logger             = $logger;
    }

    public function generate()
    {
        $baseUrl = $this->appConfig->getBaseUrl();

        if (strpos($baseUrl, 'http') === false) {
            throw new ServiceException('Please, set "base_url" parameter to full URL (with protocol) in config file init.php');
        }

        // Create sitemap
        $this->sitemap = new Sitemap($this->getSitemapFilePath());

        // Recursively iterate over all ifaces
        $this->iterateLayer();

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

    protected function iterateLayer(IFaceModelInterface $parent = null)
    {
        // Get all available IFaces in layer
        $ifaceModels = $this->ifaceModelProvider->getLayer($parent);

        // Iterate over all IFaces
        foreach ($ifaceModels as $ifaceModel) {
            // Skip hidden ifaces
            if ($ifaceModel->hideInSiteMap()) {
                continue;
            }

            $iface = $this->ifaceHelper->createIFaceFromModel($ifaceModel);

            // TODO Deal with calculation of the last_modified
            $urls = $this->ifaceHelper->getPublicAvailableUrls($iface, $this->urlParameters);

            foreach ($urls as $url) {
                if (!$url) {
                    continue;
                }

                $this->logger->debug('Found url :value', [':value' => $url]);
                // Store URL
                $this->sitemap->addItem($url);
                $this->linksCounter++;
            }

            $this->iterateLayer($ifaceModel);
        }
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
        return MultiSite::instance()->docRoot();
    }
}
