<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate;
use BetaKiller\IFace\Url\UrlDispatcher;
use BetaKiller\IFace\Url\UrlParametersInterface;
use BetaKiller\IFace\Url\UrlPrototype;
use BetaKiller\Service;
use BetaKiller\Service\ServiceException;
use samdark\sitemap\Index;
use samdark\sitemap\Sitemap;

class Service_Sitemap extends Service
{
    use BetaKiller\Helper\IFaceTrait;

    /**
     * @var UrlParametersInterface
     */
    protected $_url_parameters;

    /**
     * @var UrlDispatcher
     */
    protected $_url_dispatcher;

    /**
     * @var IFaceModelProviderAggregate
     */
    protected $_iface_model_provider;

    /**
     * @var \samdark\sitemap\Sitemap
     */
    protected $_sitemap;

    /**
     * @var int
     */
    protected $_links_counter;

    /**
     * Service_Sitemap constructor.
     *
     * @param UrlParametersInterface      $_url_parameters
     * @param IFaceModelProviderAggregate $_iface_model_provider
     */
    public function __construct(UrlParametersInterface $_url_parameters, IFaceModelProviderAggregate $_iface_model_provider)
    {
        $this->_url_parameters       = $_url_parameters;
        $this->_iface_model_provider = $_iface_model_provider;
    }

    public function generate()
    {
        $base_url = Kohana::$base_url;

        if (strpos($base_url, 'http') === false)
            throw new ServiceException('Please, set "base_url" parameter to full URL (with protocol) in config file init.php');

        // create sitemap
        $this->_sitemap = new Sitemap($this->get_sitemap_file_path());

        // Recursively iterate over all ifaces
        $this->iterate_layer();

        // write it
        $this->_sitemap->write();


        $sitemapFiles = $this->_sitemap->getSitemapUrls($base_url);

        if (count($sitemapFiles) > 1) {
            // create sitemap index file
            $index = new Index($this->get_sitemap_index_file_path());

            // add URLs
            foreach ($sitemapFiles as $sitemapUrl) {
                $index->addSitemap($sitemapUrl);
            }

            // write it
            $index->write();
        }

        $this->info($this->_links_counter.' links have been written to sitemap.xml');

        return $this;
    }

    protected function iterate_layer(IFaceModelInterface $parent = null)
    {
        // Get all available IFaces in layer
        $iface_models = $this->_iface_model_provider->getLayer($parent);

        // Iterate over all IFaces
        foreach ($iface_models as $iface_model) {
            // Skip hidden ifaces
            if ($iface_model->hideInSiteMap())
                continue;

            if ($iface_model->hasDynamicUrl()) {
                $prototype = UrlPrototype::instance()->parse($iface_model->getUri());

                $model_name    = $prototype->getModelName();
                $model_key     = $prototype->getModelKey();
                $urlDataSource = $prototype->getModelInstance();

                $items = $urlDataSource->getAvailableItemsByUrlKey($model_key, $this->_url_parameters);

                foreach ($items as $item) {
                    // Save current item to parameters registry
                    $this->_url_parameters->set($model_name, $item, true);

                    // Make dynamic URL + recursion
                    $this->process_iface_model($iface_model);
                }
            } else {
                // Make static URL + recursion
                $this->process_iface_model($iface_model);
            }
        }
    }

    protected function process_iface_model(IFaceModelInterface $model)
    {
        $iface = $this->iface_from_model($model);

        // Get current item full URL
        $url = $iface->url($this->_url_parameters);

        // TODO Force calculation of the last_modified
        $last_modified = $iface->getLastModified();
        $timestamp     = $last_modified ? $last_modified->getTimestamp() : null;

        // Store URL
        $this->_sitemap->addItem($url, $timestamp);

        $this->_links_counter++;

        // Recursion
        $this->iterate_layer($model);
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
