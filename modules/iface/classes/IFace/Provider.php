<?php defined('SYSPATH') OR die('No direct script access.');

class IFace_Provider implements IFace_Provider_Interface {

    use Util_Singleton;

    /**
     * @var IFace_Provider_Source[]
     */
    protected $_sources;

    protected function __construct()
    {
        // TODO get actual sources from config

        $this->_sources = array(
            IFace_Provider_Source::factory('DB'),
            IFace_Provider_Source::factory('Admin'),
        );
    }

    /**
     * Returns default iface model in current provider
     *
     * @return IFace_Model|null
     * @throws IFace_Exception
     */
    public function get_default()
    {
        $default_iface = $this->source_exec('get_default');

        if ( ! $default_iface )
            throw new IFace_Exception('No default IFace found');

        return $default_iface;
    }

    /**
     * Returns iface model by codename or NULL if none was found
     *
     * @todo cache results and return (clone) $model
     * @param $codename
     * @return IFace_Model|null
     * @throws IFace_Exception
     */
    public function by_codename($codename)
    {
        $iface_model = $this->source_exec('by_codename', $codename);

        if ( ! $iface_model )
            throw new IFace_Exception('No IFace found by codename :codename', array(':codename' => $codename));

        return $iface_model;
    }

    /**
     * Performs iface model search by uri (and optional parent iface model)
     *
     * @param string $uri
     * @param IFace_Model|null $parent_model
     * @return IFace_Model
     */
    public function by_uri($uri, IFace_Model $parent_model = NULL)
    {
        $layer = $parent_model
            ? $parent_model->get_children()
            : $this->get_root();

        // First iteration through static urls
        foreach ( $layer as $child )
        {
            // Find iface by concrete uri
            if ( $child->get_uri() == $uri )
                return $child;
        }

        // @TODO iteration through dynamic urls like "<category_url>"

        // Nothing found
        return NULL;
    }

    /**
     * Returns list of root elements
     *
     * @return IFace_Model[]
     */
    public function get_root()
    {
        $models = array();

        foreach ( $this->_sources as $source )
        {
            $models = array_merge($models, $source->get_root());
        }

        return $models;
    }

    protected function source_exec($method)
    {
        $value = NULL;

        $call_args = func_get_args();
        array_shift($call_args);

        foreach ( $this->_sources as $source )
        {
            $value = call_user_func_array(array($source, $method), $call_args);

            if ( $value )
                break;
        }

        return $value;
    }

}