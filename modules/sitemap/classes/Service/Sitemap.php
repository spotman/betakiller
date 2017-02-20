<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Service;
use BetaKiller\Service\ServiceException;
use samdark\sitemap\Sitemap;
use samdark\sitemap\Index;

class Service_Sitemap extends Service
{
    use BetaKiller\Helper\IFaceTrait;

    /**
     * @var URL_Parameters
     */
    protected $_url_parameters;

    /**
     * @var URL_Dispatcher
     */
    protected $_url_dispatcher;

    /**
     * @var IFace_Model_Provider
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
     * @param URL_Parameters $_url_parameters
     * @param URL_Dispatcher $_url_dispatcher
     * @param IFace_Model_Provider $_iface_model_provider
     */
    public function __construct(URL_Parameters $_url_parameters, URL_Dispatcher $_url_dispatcher, IFace_Model_Provider $_iface_model_provider)
    {
        $this->_url_parameters       = $_url_parameters;
        $this->_url_dispatcher       = $_url_dispatcher;
        $this->_iface_model_provider = $_iface_model_provider;
    }

    public function generate()
    {
        $base_url = Kohana::$base_url;

        if ( strpos($base_url, 'http') === FALSE )
            throw new ServiceException('Please, set "base_url" parameter to full URL (with protocol) in config file init.php');

        // create sitemap
        $this->_sitemap = new Sitemap($this->get_sitemap_file_path());

        // Recursively iterate over all ifaces
        $this->iterate_layer();

        // write it
        $this->_sitemap->write();


        $sitemapFiles = $this->_sitemap->getSitemapUrls($base_url);

        if ( count($sitemapFiles) > 1 )
        {
            // create sitemap index file
            $index = new Index($this->get_sitemap_index_file_path());

            // add URLs
            foreach ( $sitemapFiles as $sitemapUrl )
            {
                $index->addSitemap($sitemapUrl);
            }

            // write it
            $index->write();
        }

        $this->info($this->_links_counter.' links have been written to sitemap.xml');

        return $this;
    }

    protected function iterate_layer(IFaceModelInterface $parent = NULL)
    {
        // Get all available IFaces in layer
        $iface_models = $this->_iface_model_provider->get_layer($parent);

        // Iterate over all IFaces
        foreach ($iface_models as $iface_model)
        {
            // Skip hidden ifaces
            if ( $iface_model->hide_in_site_map() )
                continue;

            if ( $iface_model->has_dynamic_url()  )
            {
                $prototype = URL_Prototype::instance()->parse($iface_model->get_uri());

                $model_name = $prototype->get_model_name();
                $model_key = $prototype->get_model_key();

                $items = $this->model_factory($model_name)
                    ->get_available_items_by_url_key($model_key, $this->_url_parameters);

                foreach ($items as $item)
                {
                    // Save current item to parameters registry
                    $this->_url_parameters->set($model_name, $item, TRUE);

                    // Make dynamic URL + recursion
                    $this->process_iface_model($iface_model);
                }
            }
            else
            {
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
        $timestamp = $last_modified ? $last_modified->getTimestamp() : NULL;

        // Store URL
        $this->_sitemap->addItem($url, $timestamp);

        $this->_links_counter++;

        // Recursion
        $this->iterate_layer($model);
    }

    protected function model_factory($name)
    {
        return $this->_url_dispatcher->model_factory($name);
    }

    public function serve(Response $response)
    {
        $content = file_get_contents($this->get_sitemap_file_path());
        $response->send_string($content, $response::XML);
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
        return MultiSite::instance()->doc_root();
    }
}
