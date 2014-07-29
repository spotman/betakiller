<?php defined('SYSPATH') OR die('No direct script access.');

class IFace_Provider {

    use Util_Singleton;

    /**
     * Performs parsing of requested url
     * Returns IFace
     *
     * @param $uri
     * @return IFace|null
     * @throws IFace_Exception_MissingURL
     */
    public function parse_uri($uri)
    {
        $uri_parts = $uri ? explode('/', $uri) : NULL;

        // Root requested - search for default IFace
        if ( ! $uri_parts )
        {
            return $this->get_default();
        }

        $parent_iface = NULL;
        $iface_instance = NULL;

        // Dispatch childs
        // Loop through every uri part and initialize it`s iface
        foreach ( $uri_parts as $uri_part )
        {
            // Prevent XSS via URL
            $uri_part = HTML::chars(strip_tags($uri_part));

            $iface_instance = $this->by_uri($uri_part, $parent_iface);

            // Throw IFace_Exception_MissingURL so we can forward user to parent iface or custom 404 page
            if ( ! $iface_instance )
                throw new IFace_Exception_MissingURL($uri_part, $parent_iface);

            // Store link to parent IFace if exists
            if ( $parent_iface)
                $iface_instance->parent($parent_iface);

            $parent_iface = $iface_instance;
        }

        // Return last IFace
        return $iface_instance;
    }

    /**
     * Performs iface search by uri (and optional parent iface model)
     *
     * @param string $uri
     * @param IFace|NULL $parent_iface
     * @return IFace
     * @throws IFace_Exception
     */
    public function by_uri($uri, IFace $parent_iface = NULL)
    {
        $model_provider = $this->model_provider();

        $parent_iface_model = $parent_iface ? $parent_iface->model() : NULL;

        $layer = $model_provider->get_layer($parent_iface_model);

        if ( ! $layer )
            throw new IFace_Exception('Empty layer for :codename IFace',
                array(':codename' => $parent_iface->codename())
            );

        $iface_model = NULL;
        $dynamic_model = NULL;

        // First iteration through static urls
        foreach ( $layer as $iface_model )
        {
            // Skip ifaces with dynamic urls
            if ( $iface_model->has_dynamic_url() )
            {
                if ( $dynamic_model )
                    throw new IFace_Exception('Layer must have only one IFace with dynamic dispatching');

                $dynamic_model = $iface_model;
                continue;
            }

            // IFace found by concrete uri
            if ( $iface_model->get_uri() == $uri )
                return $this->iface_factory($iface_model);
        }

        // Second iteration for dynamic urls
        if ( $dynamic_model )
        {
            URL_Dispatcher::instance()->parse_uri($dynamic_model->get_uri(), $uri);

            /** @var IFace_Dispatchable $iface_instance */
            $iface_instance = $this->iface_factory($dynamic_model);

            return $iface_instance;
        }

        // Nothing found
        return NULL;
    }

    public function get_default()
    {
        $default_model = $this->model_provider()->get_default();

        return $this->iface_factory($default_model);
    }

    protected function iface_factory(IFace_Model $model)
    {
        return IFace::factory($model);
    }

    /**
     * @return IFace_Model_Provider
     */
    protected function model_provider()
    {
        return IFace_Model_Provider::instance();
    }

}