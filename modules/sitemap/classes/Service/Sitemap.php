<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate;
use BetaKiller\IFace\Url\UrlParametersInterface;
use BetaKiller\IFace\Url\UrlPrototypeHelper;
use BetaKiller\Service;
use BetaKiller\Service\ServiceException;
use samdark\sitemap\Index;
use samdark\sitemap\Sitemap;

class Service_Sitemap extends Service
{
    use \BetaKiller\Helper\LogTrait;

    /**
     * @var UrlParametersInterface
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
     * @param \BetaKiller\IFace\Url\UrlParametersInterface                $urlParameters
     * @param \BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate $ifaceModelProvider
     * @param \BetaKiller\Helper\IFaceHelper                              $ifaceHelper
     */
    public function __construct(
        UrlParametersInterface $urlParameters,
        IFaceModelProviderAggregate $ifaceModelProvider,
        IFaceHelper $ifaceHelper
    )
    {
        $this->urlParameters      = $urlParameters;
        $this->ifaceModelProvider = $ifaceModelProvider;
        $this->ifaceHelper        = $ifaceHelper;
    }

    public function generate()
    {
        $base_url = Kohana::$base_url;

        if (strpos($base_url, 'http') === false) {
            throw new ServiceException('Please, set "base_url" parameter to full URL (with protocol) in config file init.php');
        }

        // Create sitemap
        $this->sitemap = new Sitemap($this->get_sitemap_file_path());

        // Recursively iterate over all ifaces
        $this->iterate_layer();

        // Write sitemap files
        $this->sitemap->write();

        $sitemapFiles = $this->sitemap->getSitemapUrls($base_url);

        if (count($sitemapFiles) > 1) {
            // Create sitemap index file
            $index = new Index($this->get_sitemap_index_file_path());

            // Add URLs
            foreach ($sitemapFiles as $sitemapUrl) {
                $index->addSitemap($sitemapUrl);
            }

            // Write index
            $index->write();
        }

        $this->info($this->linksCounter.' links have been written to sitemap.xml');

        return $this;
    }

    protected function iterate_layer(IFaceModelInterface $parent = null)
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
            $urls = $iface->getAvailableUrls($this->urlParameters);

            foreach ($urls as $url) {
                // Store URL
                $this->sitemap->addItem($url);
                $this->linksCounter++;
            }

            $this->iterate_layer($ifaceModel);
        }
    }

    public function serve(Response $response)
    {
        $content = file_get_contents($this->get_sitemap_file_path());
        $response->send_string($content, $response::TYPE_XML);
    }

    protected function get_sitemap_file_path()
    {
        return $this->get_document_root_path().DIRECTORY_SEPARATOR.'sitemap.xml';
    }

    protected function get_sitemap_index_file_path()
    {
        return $this->get_document_root_path().DIRECTORY_SEPARATOR.'sitemap_index.xml';
    }

    protected function get_document_root_path()
    {
        return MultiSite::instance()->docRoot();
    }
}
