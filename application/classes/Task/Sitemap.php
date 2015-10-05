<?php defined('SYSPATH') OR die('No direct script access.');

use samdark\sitemap\Sitemap;
use samdark\sitemap\Index;

class Task_Sitemap extends Minion_Task {

    /**
     * @var URL_Parameters
     */
    protected $_url_parameters;

    /**
     * @var URL_Dispatcher
     */
    protected $_url_dispatcher;

    /**
     * @var \samdark\sitemap\Sitemap
     */
    protected $_sitemap;

    /**
     * @var int
     */
    protected $_links_counter;

    protected function _execute(array $params)
    {
        $this->_url_parameters = URL_Parameters::factory();
        $this->_url_dispatcher = URL_Dispatcher::instance();

        $doc_root = MultiSite::instance()->doc_root();
        $base_url = Kohana::$base_url;

        if ( strpos($base_url, 'http') === FALSE )
            throw new Minion_Exception('Please, set "base_url" parameter to full URL (with protocol) in config file init.php');

        // create sitemap
        $this->_sitemap = new Sitemap($doc_root.DIRECTORY_SEPARATOR.'sitemap.xml');

        // Recursively iterate over all ifaces
        $this->iterate_layer();

        // write it
        $this->_sitemap->write();


        $sitemapFiles = $this->_sitemap->getSitemapUrls($base_url);

        if ( count($sitemapFiles) > 1 )
        {
            // create sitemap index file
            $index = new Index($doc_root. DIRECTORY_SEPARATOR.'sitemap_index.xml');

            // add URLs
            foreach ( $sitemapFiles as $sitemapUrl )
            {
                $index->addSitemap($sitemapUrl);
            }

            // write it
            $index->write();
        }

        $this->notice($this->_links_counter.' links have been written');
    }

    protected function iterate_layer(IFace_Model $parent = NULL)
    {
        // Get all available IFaces in layer
        $iface_models = IFace_Model_Provider::instance()->get_layer($parent);

        // Iterate over all IFaces
        foreach ($iface_models as $iface_model)
        {
            // Skip hidden ifaces
            if ( $iface_model->hide_in_site_map() )
                continue;

            if ( $iface_model->has_dynamic_url()  )
            {
                $prototype = URL_Prototype::factory()->parse($iface_model->get_uri());

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

    protected function process_iface_model(IFace_Model $model)
    {
        $iface = IFace::factory($model);

        // Get current item full URL
        $url = $iface->url($this->_url_parameters, TRUE);

        $last_modified = $iface->get_last_modified();
        $timestamp = $last_modified ? $last_modified->getTimestamp() : NULL;

        // Store URL
        $this->_sitemap->addItem($url, $timestamp);

        $this->_links_counter++;

        // Recursion
        $this->iterate_layer($model);
    }

    protected function model_factory($name)
    {
        return URL_Dispatcher::instance()->model_factory($name);
    }
}
